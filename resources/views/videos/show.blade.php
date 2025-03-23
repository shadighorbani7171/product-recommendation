<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <video-player
                        :video="{{ json_encode($video) }}"
                        :stream-data="{{ json_encode($streamData) }}"
                        @progress="handleProgress"
                        @buffering="handleBuffering"
                    ></video-player>

                    <!-- Video Actions -->
                    <div class="mt-6 flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <button
                                @click="likeVideo"
                                class="flex items-center space-x-2 text-gray-600 hover:text-red-600"
                                :class="{ 'text-red-600': isLiked }"
                            >
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                                </svg>
                                <span>{{ likesCount }}</span>
                            </button>

                            <button
                                @click="shareVideo"
                                class="flex items-center space-x-2 text-gray-600 hover:text-blue-600"
                            >
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
                                </svg>
                                <span>Share</span>
                            </button>
                        </div>

                        @can('delete', $video)
                            <form action="{{ route('videos.destroy', $video) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-red-600 hover:text-red-800"
                                    onclick="return confirm('Are you sure you want to delete this video?')"
                                >
                                    Delete Video
                                </button>
                            </form>
                        @endcan
                    </div>

                    <!-- Video Stats -->
                    <div class="mt-6 grid grid-cols-3 gap-4 text-center">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-semibold">{{ $video->views_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Views</div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-semibold">{{ $video->likes_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Likes</div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-semibold">{{ $video->shares_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Shares</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        import VideoPlayer from '@/components/VideoPlayer.vue';

        export default {
            components: {
                VideoPlayer
            },
            data() {
                return {
                    isLiked: false,
                    likesCount: {{ $video->likes_count ?? 0 }},
                    progress: 0,
                    bufferingDuration: 0
                };
            },
            methods: {
                async likeVideo() {
                    try {
                        const response = await axios.post(`/videos/${this.video.id}/like`);
                        this.isLiked = response.data.isLiked;
                        this.likesCount = response.data.likesCount;
                    } catch (error) {
                        console.error('Failed to like video:', error);
                    }
                },
                async shareVideo() {
                    try {
                        await navigator.share({
                            title: this.video.title,
                            text: this.video.description,
                            url: window.location.href
                        });
                    } catch (error) {
                        console.error('Failed to share video:', error);
                    }
                },
                handleProgress(progress) {
                    this.progress = progress;
                },
                handleBuffering(duration) {
                    this.bufferingDuration += duration;
                }
            }
        };
    </script>
    @endpush
</x-app-layout>
