<template>
    <div class="video-player">
        <div class="relative aspect-video bg-black">
            <video
                ref="videoPlayer"
                class="w-full h-full"
                controls
                preload="metadata"
                @error="handleError"
                @loadedmetadata="handleMetadata"
                @timeupdate="handleTimeUpdate"
                @waiting="handleBuffering"
                @playing="handlePlaying"
            >
                <source
                    :src="streamData.stream_url"
                    type="application/x-mpegURL"
                />
                <p class="text-white p-4">
                    Your browser does not support the video tag.
                </p>
            </video>

            <!-- Quality Selector -->
            <div
                v-if="showQualitySelector"
                class="absolute top-4 right-4 bg-black bg-opacity-75 rounded-lg p-2"
            >
                <select
                    v-model="selectedQuality"
                    class="bg-transparent text-white border border-white rounded px-2 py-1"
                    @change="changeQuality"
                >
                    <option
                        v-for="(label, value) in streamData.quality_levels"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </div>

            <!-- Loading Indicator -->
            <div
                v-if="isBuffering"
                class="absolute inset-0 flex items-center justify-center"
            >
                <div
                    class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"
                ></div>
            </div>

            <!-- Error Message -->
            <div
                v-if="error"
                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-75"
            >
                <div class="text-white text-center p-4">
                    <p class="mb-2">{{ error }}</p>
                    <button
                        @click="retryPlayback"
                        class="bg-white text-black px-4 py-2 rounded hover:bg-gray-200"
                    >
                        Retry
                    </button>
                </div>
            </div>
        </div>

        <!-- Video Info -->
        <div class="mt-4">
            <h2 class="text-xl font-semibold">{{ video.title }}</h2>
            <p v-if="video.description" class="text-gray-600 mt-2">
                {{ video.description }}
            </p>
            <div class="flex items-center mt-2 text-sm text-gray-500">
                <span>{{ formatDuration(video.duration) }}</span>
                <span class="mx-2">•</span>
                <span>{{ formatFileSize(video.file_size) }}</span>
            </div>
        </div>
    </div>
</template>

<script>
import Hls from "hls.js";

export default {
    name: "VideoPlayer",

    props: {
        video: {
            type: Object,
            required: true,
        },
        streamData: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            hls: null,
            selectedQuality: "auto",
            isBuffering: false,
            error: null,
            showQualitySelector: false,
            bufferingStartTime: null,
            bufferingDuration: 0,
        };
    },

    mounted() {
        this.initializePlayer();
    },

    beforeDestroy() {
        this.destroyPlayer();
    },

    methods: {
        initializePlayer() {
            const video = this.$refs.videoPlayer;

            if (Hls.isSupported()) {
                this.hls = new Hls({
                    debug: false,
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 90,
                    maxBufferLength: 30,
                    maxMaxBufferLength: 600,
                    maxBufferSize: 60 * 1000 * 1000,
                    maxBufferHole: 0.5,
                    lowLatencyMode: true,
                    manifestLoadPolicy: {
                        default: {
                            maxTimeToFirstByteMs: 10000,
                            maxLoadTimeMs: 20000,
                            timeoutRetry: {
                                maxNumRetry: 6,
                                retryDelayMs: 1000,
                                maxRetryDelayMs: 8000,
                            },
                            errorRetry: {
                                maxNumRetry: 6,
                                retryDelayMs: 1000,
                                maxRetryDelayMs: 8000,
                            },
                        },
                    },
                });

                this.hls.loadSource(this.streamData.stream_url);
                this.hls.attachMedia(video);
                this.hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    video.play().catch((error) => {
                        console.error("Auto-play failed:", error);
                    });
                });

                // Handle quality level changes
                this.hls.on(Hls.Events.LEVEL_SWITCHED, (event, data) => {
                    this.selectedQuality = data.level;
                });
            } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
                // For Safari, which has native HLS support
                video.src = this.streamData.stream_url;
            }

            // Preload first few seconds
            if (this.streamData.preload_url) {
                const preloadLink = document.createElement("link");
                preloadLink.rel = "preload";
                preloadLink.as = "video";
                preloadLink.href = this.streamData.preload_url;
                document.head.appendChild(preloadLink);
            }
        },

        destroyPlayer() {
            if (this.hls) {
                this.hls.destroy();
                this.hls = null;
            }
        },

        handleError(event) {
            console.error("Video error:", event);
            this.error = "Failed to load video. Please try again.";
        },

        handleMetadata() {
            this.showQualitySelector = true;
        },

        handleTimeUpdate() {
            // Track playback progress
            const video = this.$refs.videoPlayer;
            const progress = (video.currentTime / video.duration) * 100;
            this.$emit("progress", progress);
        },

        handleBuffering() {
            this.isBuffering = true;
            if (!this.bufferingStartTime) {
                this.bufferingStartTime = Date.now();
            }
        },

        handlePlaying() {
            this.isBuffering = false;
            if (this.bufferingStartTime) {
                this.bufferingDuration += Date.now() - this.bufferingStartTime;
                this.bufferingStartTime = null;
                this.$emit("buffering", this.bufferingDuration);
            }
        },

        changeQuality() {
            if (this.hls) {
                if (this.selectedQuality === "auto") {
                    this.hls.currentLevel = -1;
                } else {
                    const level = this.hls.levels.findIndex(
                        (level) =>
                            level.height === parseInt(this.selectedQuality)
                    );
                    if (level !== -1) {
                        this.hls.currentLevel = level;
                    }
                }
            }
        },

        retryPlayback() {
            this.error = null;
            this.destroyPlayer();
            this.initializePlayer();
        },

        formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = seconds % 60;

            if (hours > 0) {
                return `${hours}:${minutes
                    .toString()
                    .padStart(2, "0")}:${remainingSeconds
                    .toString()
                    .padStart(2, "0")}`;
            }
            return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
        },

        formatFileSize(bytes) {
            const sizes = ["Bytes", "KB", "MB", "GB"];
            if (bytes === 0) return "0 Byte";
            const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
        },
    },
};
</script>

<style scoped>
.video-player {
    max-width: 1200px;
    margin: 0 auto;
}

.aspect-video {
    aspect-ratio: 16 / 9;
}
</style>
