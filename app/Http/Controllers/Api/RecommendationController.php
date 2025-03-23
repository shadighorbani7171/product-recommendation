<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductInteraction;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecommendationController extends Controller
{
    /**
     * Get recommended products based on user preferences and behavior
     */
    public function getRecommendations(Request $request)
    {
        $user = $request->user();
        $priceRange = $this->getUserPriceRange($user);
        
        // Get user's preferred categories
        $preferredCategories = [];
        if ($user) {
            $preferences = UserPreference::where('user_id', $user->id)->first();
            $preferredCategories = $preferences ? $preferences->preferred_categories : [];
        }

        // Build recommendation query
        $recommendations = Product::query()
            ->when($priceRange, function ($query, $priceRange) {
                return $query->whereBetween('price', [$priceRange['min'], $priceRange['max']]);
            })
            ->when($preferredCategories, function ($query, $categories) {
                return $query->whereIn('category', $categories);
            })
            ->select('products.*')
            ->selectRaw('COUNT(*) as category_count')
            ->leftJoin('products as p2', 'products.category', '=', 'p2.category')
            ->groupBy('products.id')
            ->orderByDesc('category_count')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Get purchase-based recommendations if available
        if ($user) {
            $purchaseBasedRecommendations = $this->getPurchaseBasedRecommendations($user);
            $recommendations = $recommendations->merge($purchaseBasedRecommendations)->unique('id')->take(10);
        }

        return response()->json([
            'recommendations' => $recommendations,
            'price_range' => $priceRange
        ]);
    }

    /**
     * Get recommendations based on a specific product
     */
    public function getSimilarProducts(Request $request, Product $product)
    {
        $similarProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        return response()->json($similarProducts);
    }

    /**
     * Get trending products with time filter
     */
    public function getTrendingProducts(Request $request)
    {
        $timeFrame = $request->input('time_frame', 'week'); // Options: day, week, month
        
        $startDate = match($timeFrame) {
            'day' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfWeek()
        };

        $trendingProducts = ProductInteraction::where('created_at', '>=', $startDate)
            ->where('type', 'view')
            ->select('product_id', DB::raw('count(*) as view_count'))
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->with('product')
            ->get()
            ->pluck('product');

        return response()->json([
            'trending_products' => $trendingProducts,
            'time_frame' => $timeFrame
        ]);
    }

    /**
     * Get recommendations based on search history
     */
    public function getSearchBasedRecommendations(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Get user's recent searches
        $recentSearches = ProductInteraction::where('user_id', $user->id)
            ->where('type', 'search')
            ->whereNotNull('search_query')
            ->orderByDesc('created_at')
            ->limit(5)
            ->pluck('search_query');

        // Find products matching recent search terms
        $recommendations = collect();
        foreach ($recentSearches as $search) {
            $products = Product::where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->limit(3)
                ->get();
            $recommendations = $recommendations->merge($products);
        }

        return response()->json([
            'search_based_recommendations' => $recommendations->unique('id')->values()
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validated = $request->validate([
            'preferred_categories' => 'required|array',
            'preferred_categories.*' => 'string'
        ]);

        UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            ['preferred_categories' => $validated['preferred_categories']]
        );

        return response()->json(['message' => 'Preferences updated successfully']);
    }

    /**
     * Record user feedback (like/dislike)
     */
    public function recordFeedback(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validated = $request->validate([
            'type' => 'required|in:like,dislike'
        ]);

        ProductInteraction::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'type' => $validated['type']
        ]);

        return response()->json(['message' => 'Feedback recorded successfully']);
    }

    /**
     * Get recommendations based on purchase history
     */
    private function getPurchaseBasedRecommendations($user)
    {
        // Get categories of purchased products
        $purchasedCategories = ProductInteraction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->join('products', 'products.id', '=', 'product_interactions.product_id')
            ->pluck('category')
            ->unique();

        // Get similar products from those categories
        return Product::whereIn('category', $purchasedCategories)
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('product_id')
                    ->from('product_interactions')
                    ->where('user_id', $user->id)
                    ->where('type', 'purchase');
            })
            ->orderByDesc('views')
            ->limit(5)
            ->get();
    }

    private function getUserPriceRange($user)
    {
        if (!$user) {
            return null;
        }

        $priceStats = Product::select(
            DB::raw('AVG(price) as avg_price'),
            DB::raw('STDDEV(price) as std_dev')
        )
        ->whereIn('id', function($query) use ($user) {
            $query->select('product_id')
                ->from('product_interactions')
                ->where('user_id', $user->id)
                ->whereIn('type', ['view', 'purchase']);
        })
        ->first();

        if (!$priceStats->avg_price) {
            return null;
        }

        return [
            'min' => max(0, $priceStats->avg_price - $priceStats->std_dev),
            'max' => $priceStats->avg_price + $priceStats->std_dev
        ];
    }
} 