<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.appearance.themes.index.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.appearance.themes.index.before') !!}

    <!-- Page Header -->
    <div class="flex items-start justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.appearance.themes.index.title')
            </p>

            <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                @lang('admin::app.appearance.themes.index.info')
            </p>
        </div>

        @if (bouncer()->hasPermission('appearance.themes.upload'))
            <v-appearance-theme-upload>
                <button
                    type="button"
                    class="primary-button"
                >
                    @lang('admin::app.appearance.themes.upload.btn')
                </button>
            </v-appearance-theme-upload>
        @endif
    </div>

    {!! view_render_event('bagisto.admin.appearance.themes.list.before') !!}

    <v-appearance-themes
        :themes='@json($themes)'
        :channels='@json($channels->map(fn ($channel) => ['id' => $channel->id, 'name' => $channel->name])->values())'
    >
        <x-admin::shimmer.image class="mt-8 h-75 w-full rounded-sm" />
    </v-appearance-themes>

    {!! view_render_event('bagisto.admin.appearance.themes.list.after') !!}

    {!! view_render_event('bagisto.admin.appearance.themes.schedules.before') !!}

    <v-appearance-theme-schedules
        :schedules='@json($schedules)'
        :themes='@json($themes->map(fn ($theme) => ['code' => $theme['code'], 'name' => $theme['name']])->values())'
        :channels='@json($channels->map(fn ($channel) => ['id' => $channel->id, 'name' => $channel->name])->values())'
    ></v-appearance-theme-schedules>

    {!! view_render_event('bagisto.admin.appearance.themes.schedules.after') !!}

    {!! view_render_event('bagisto.admin.appearance.themes.index.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-appearance-themes-template"
        >
            <div class="mt-8 flex flex-col gap-8">
                <div
                    v-for="group in groups"
                    :key="group.key"
                >
                    <!-- Group Heading -->
                    <div class="flex items-center gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @{{ group.label }}
                        </p>

                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-950 dark:text-gray-300">
                            @{{ group.themes.length }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-6">
                        <div
                            class="box-shadow flex flex-col overflow-hidden rounded-sm bg-white dark:bg-gray-900"
                            v-for="theme in group.themes"
                            :key="theme.code"
                        >
                            <!-- Screenshot -->
                            <div class="relative flex h-47.5 items-center justify-center bg-gray-100 dark:bg-gray-950">
                                <img
                                    class="h-full w-full object-cover"
                                    :src="theme.screenshot"
                                    :alt="theme.name"
                                    v-if="theme.screenshot && ! failed[theme.code]"
                                    v-on:error="failed[theme.code] = true"
                                />

                                <span
                                    class="icon-image text-5xl text-gray-400"
                                    v-else
                                ></span>

                                <!-- Active Badge -->
                                <span
                                    class="label-active absolute top-3 ltr:left-3 rtl:right-3"
                                    v-if="theme.status === 'active'"
                                >
                                    @lang('admin::app.appearance.themes.index.active')
                                </span>
                            </div>

                            <!-- Body -->
                            <div class="flex flex-1 flex-col gap-2 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        @{{ theme.name }}
                                    </p>

                                    <p
                                        class="flex shrink-0 items-center gap-1 text-xs text-gray-500 dark:text-gray-300"
                                        v-if="theme.rating"
                                    >
                                        <span class="icon-star-fill text-sm text-yellow-500"></span>

                                        @{{ theme.rating }}
                                    </p>
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-300">
                                    <template v-if="theme.author">
                                        @lang('admin::app.appearance.themes.index.by') @{{ theme.author }}
                                    </template>

                                    <template v-if="theme.version">
                                        <template v-if="theme.author">·</template> v@{{ theme.version }}
                                    </template>
                                </p>

                                <p
                                    class="line-clamp-3 text-xs text-gray-600 dark:text-gray-300"
                                    v-if="theme.description"
                                >
                                    @{{ theme.description }}
                                </p>

                                <!-- Active Channels -->
                                <p
                                    class="mt-1 text-xs font-medium text-green-600"
                                    v-if="isEverywhere(theme)"
                                >
                                    @lang('admin::app.appearance.themes.index.active-on-all')
                                </p>

                                <p
                                    class="mt-1 text-xs font-medium text-green-600"
                                    v-else-if="theme.active_on.length"
                                >
                                    @lang('admin::app.appearance.themes.index.active-on')
                                    @{{ theme.active_on.map(channel => channel.name).join(', ') }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-gray-400"
                                    v-else-if="theme.is_installed"
                                >
                                    @lang('admin::app.appearance.themes.index.not-in-use')
                                </p>

                                <!-- Actions -->
                                <div class="mt-auto flex w-full flex-wrap items-center gap-2 pt-3">
                                    @if (bouncer()->hasPermission('appearance.themes.activate'))
                                        <button
                                            type="button"
                                            class="primary-button"
                                            v-if="theme.is_installed && availableChannels(theme).length"
                                            @click="openActivate(theme)"
                                        >
                                            @lang('admin::app.appearance.themes.index.activate-btn')
                                        </button>
                                    @endif

                                    <form
                                        method="POST"
                                        :action="'{{ route('admin.appearance.themes.preview', ['code' => '__CODE__']) }}'.replace('__CODE__', theme.code)"
                                        target="_blank"
                                        v-if="theme.is_installed && ! isEverywhere(theme)"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="secondary-button"
                                        >
                                            @lang('admin::app.appearance.themes.index.preview-btn')
                                        </button>
                                    </form>

                                    @if (bouncer()->hasPermission('appearance.sections'))
                                        <a
                                            class="secondary-button"
                                            :href="'{{ route('admin.appearance.sections.index', ['code' => '__CODE__']) }}'.replace('__CODE__', theme.code)"
                                            v-if="theme.status === 'active'"
                                        >
                                            @lang('admin::app.appearance.themes.index.customize-btn')
                                        </a>
                                    @endif

                                    @if (bouncer()->hasPermission('appearance.themes.upload'))
                                        <a
                                            class="secondary-button"
                                            :href="'{{ route('admin.appearance.themes.images.index', ['code' => '__CODE__']) }}'.replace('__CODE__', theme.code)"
                                            v-if="theme.is_uploaded"
                                        >
                                            @lang('admin::app.appearance.themes.images.btn')
                                        </a>

                                        <button
                                            type="button"
                                            class="secondary-button"
                                            v-if="theme.can_rollback"
                                            @click="rollback(theme)"
                                        >
                                            @lang('admin::app.appearance.themes.manage.rollback-btn')
                                        </button>
                                    @endif

                                    @if (bouncer()->hasPermission('appearance.themes.delete'))
                                        <button
                                            type="button"
                                            class="secondary-button"
                                            v-if="theme.is_uploaded && ! theme.active_on.length"
                                            @click="remove(theme)"
                                        >
                                            @lang('admin::app.appearance.themes.manage.delete-btn')
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activate Confirmation -->
                <Teleport to="body">
                    <x-admin::form
                        v-slot="{ meta, errors, handleSubmit }"
                        as="div"
                    >
                        <form @submit="handleSubmit($event, activate)">
                            <x-admin::modal ref="activateModal">
                                <x-slot:header>
                                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                                        @lang('admin::app.appearance.themes.index.activate.title')
                                    </p>
                                </x-slot>

                                <x-slot:content>
                                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.appearance.themes.index.activate.info', ['theme' => '@{{ selected.name }}'])
                                    </p>

                                    <!-- Channels -->
                                    <div class="mb-4">
                                        <p class="required mb-2 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                                            @lang('admin::app.appearance.themes.index.activate.channels')
                                        </p>

                                        <div class="grid gap-2">
                                            <label
                                                class="flex cursor-pointer items-center gap-2.5 rounded-md border border-gray-200 px-3 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                                v-for="channel in availableChannels(selected)"
                                                :key="channel.id"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="peer sr-only"
                                                    :value="channel.id"
                                                    v-model="channelIds"
                                                    @change="channelError = null; loadImpact()"
                                                />

                                                <span class="icon-uncheckbox peer-checked:icon-checked text-2xl peer-checked:text-blue-600"></span>

                                                <span
                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                                    v-text="channel.name"
                                                ></span>
                                            </label>
                                        </div>

                                        <p
                                            class="mt-1 text-xs italic text-red-600"
                                            v-if="channelError"
                                            v-text="channelError"
                                        ></p>
                                    </div>

                                    <!-- Impact Warning -->
                                    <div
                                        class="mt-2 grid gap-1 rounded-sm border border-orange-200 bg-orange-50 p-3 text-xs text-orange-800 dark:border-orange-900 dark:bg-orange-950 dark:text-orange-200"
                                        v-if="impact.length"
                                    >
                                        <p
                                            v-for="row in impact"
                                            :key="row.channel_id"
                                        >
                                            @lang('admin::app.appearance.themes.index.activate.warning', [
                                                'channel' => '@{{ row.channel }}',
                                                'count' => '@{{ row.customizations }}',
                                                'theme' => '@{{ row.current_theme }}',
                                            ])
                                        </p>
                                    </div>
                                </x-slot>

                                <x-slot:footer>
                                    <x-admin::button
                                        button-type="submit"
                                        class="primary-button"
                                        :title="trans('admin::app.appearance.themes.index.activate.confirm-btn')"
                                        ::loading="isLoading"
                                        ::disabled="isLoading"
                                    />
                                </x-slot>
                            </x-admin::modal>
                        </form>
                    </x-admin::form>
                </Teleport>
            </div>
        </script>

        @if (bouncer()->hasPermission('appearance.themes.upload'))
            <script
                type="text/x-template"
                id="v-appearance-theme-upload-template"
            >
                <div>
                    <button
                        type="button"
                        class="primary-button"
                        @click="$refs.uploadModal.open()"
                    >
                        @lang('admin::app.appearance.themes.upload.btn')
                    </button>

                    <!-- Upload Theme -->
                    <Teleport to="body">
                        <x-admin::form
                            v-slot="{ meta, errors, handleSubmit }"
                            as="div"
                        >
                            <form
                                @submit="handleSubmit($event, upload)"
                                ref="uploadForm"
                                enctype="multipart/form-data"
                            >
                                <x-admin::modal ref="uploadModal">
                                    <x-slot:header>
                                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                                            @lang('admin::app.appearance.themes.upload.title')
                                        </p>
                                    </x-slot>

                                    <x-slot:content>
                                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.appearance.themes.upload.info')
                                        </p>

                                        <!-- Archive -->
                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.appearance.themes.upload.file')
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="file"
                                                name="archive"
                                                accept=".zip,application/zip"
                                                rules="required"
                                                :label="trans('admin::app.appearance.themes.upload.file')"
                                            />

                                            <x-admin::form.control-group.error control-name="archive" />

                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                                                @lang('admin::app.appearance.themes.upload.file-hint', ['size' => \Webkul\Admin\Http\Controllers\Appearance\ThemeController::MAX_UPLOAD_KILOBYTES / 1024])
                                            </p>
                                        </x-admin::form.control-group>

                                        <!-- Processing -->
                                        <p
                                            class="mb-4 rounded-sm border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200"
                                            v-if="processing"
                                            v-text="processing"
                                        ></p>

                                        <!-- Trust Warning -->
                                        <p class="rounded-sm border border-orange-200 bg-orange-50 p-3 text-xs text-orange-800 dark:border-orange-900 dark:bg-orange-950 dark:text-orange-200">
                                            @lang('admin::app.appearance.themes.upload.warning')
                                        </p>
                                    </x-slot>

                                    <x-slot:footer>
                                        <x-admin::button
                                            button-type="submit"
                                            class="primary-button"
                                            :title="trans('admin::app.appearance.themes.upload.confirm-btn')"
                                            ::loading="isLoading"
                                            ::disabled="isLoading"
                                        />
                                    </x-slot>
                                </x-admin::modal>
                            </form>
                        </x-admin::form>
                    </Teleport>
                </div>
            </script>

            <script type="module">
                app.component('v-appearance-theme-upload', {
                    template: '#v-appearance-theme-upload-template',

                    data() {
                        return {
                            isLoading: false,

                            processing: null,
                        };
                    },

                    methods: {
                        /**
                         * Send the archive, reloading the gallery once the queue has installed the theme.
                         */
                        upload(params, { setErrors }) {
                            this.isLoading = true;

                            this.processing = null;

                            this.$axios.post("{{ route('admin.appearance.themes.upload') }}", new FormData(this.$refs.uploadForm))
                                .then(response => this.settle(response, setErrors))
                                .catch(error => this.fail(error, setErrors));
                        },

                        /**
                         * Follow an installed upload to the gallery, or ask again shortly while it is still queued.
                         */
                        settle(response, setErrors) {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;

                                return;
                            }

                            this.processing = response.data.message;

                            setTimeout(() => {
                                this.$axios.get(response.data.status_url)
                                    .then(next => this.settle(next, setErrors))
                                    .catch(error => this.fail(error, setErrors));
                            }, 2000);
                        },

                        /**
                         * Show why an upload was refused against the archive field, or flash any other failure.
                         */
                        fail(error, setErrors) {
                            this.isLoading = false;

                            this.processing = null;

                            if (error.response?.status == 422) {
                                setErrors(error.response.data.errors);

                                return;
                            }

                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message ?? error.message,
                            });
                        },
                    },
                });
            </script>
        @endif

        <script type="module">
            app.component('v-appearance-themes', {
                template: '#v-appearance-themes-template',

                props: ['themes', 'channels'],

                data() {
                    return {
                        selected: {},

                        channelIds: [],

                        impact: [],

                        channelError: null,

                        failed: {},

                        isLoading: false,
                    };
                },

                computed: {
                    /**
                     * The installed themes under their heading, left out when there are none.
                     */
                    groups() {
                        return [
                            {
                                key: 'installed',
                                label: "@lang('admin::app.appearance.themes.index.my-themes')",
                                themes: this.themes.filter(theme => theme.is_installed),
                            },
                        ].filter(group => group.themes.length);
                    },
                },

                methods: {
                    /**
                     * Channels that are not already running this theme. A theme that is
                     * live everywhere has nothing left to activate.
                     */
                    availableChannels(theme) {
                        if (! theme?.active_on) {
                            return this.channels;
                        }

                        const live = theme.active_on.map(channel => channel.id);

                        return this.channels.filter(channel => ! live.includes(channel.id));
                    },

                    /**
                     * Live on every channel there is, so naming them adds nothing.
                     */
                    isEverywhere(theme) {
                        return theme.active_on.length > 0 && ! this.availableChannels(theme).length;
                    },

                    /**
                     * Open the activation confirmation for a theme, preselecting the only
                     * available channel when there is just one.
                     */
                    openActivate(theme) {
                        this.selected = theme;

                        this.impact = [];

                        this.channelError = null;

                        const available = this.availableChannels(theme);

                        this.channelIds = available.length === 1 ? [available[0].id] : [];

                        this.$refs.activateModal.open();

                        this.loadImpact();
                    },

                    /**
                     * Fetch what activating would strand, so the confirmation can name it.
                     */
                    loadImpact() {
                        if (! this.channelIds.length) {
                            this.impact = [];

                            return;
                        }

                        this.$axios.get("{{ route('admin.appearance.themes.impact', 'THEME_CODE') }}".replace('THEME_CODE', this.selected.code), {
                                params: { channel_ids: this.channelIds },
                            })
                            .then(response => {
                                this.impact = response.data.impact;
                            })
                            .catch(() => {
                                this.impact = [];
                            });
                    },

                    /**
                     * Roll an uploaded theme back to its previous version once the admin confirms.
                     */
                    rollback(theme) {
                        this.$emitter.emit('open-confirm-modal', {
                            message: "@lang('admin::app.appearance.themes.manage.rollback-confirm')",

                            agree: () => {
                                this.$axios.post("{{ route('admin.appearance.themes.rollback', 'THEME_CODE') }}".replace('THEME_CODE', theme.code))
                                    .then(response => {
                                        window.location.href = response.data.redirect_url;
                                    })
                                    .catch(error => this.flashError(error));
                            },
                        });
                    },

                    /**
                     * Delete an uploaded theme once the admin confirms.
                     */
                    remove(theme) {
                        this.$emitter.emit('open-confirm-modal', {
                            message: "@lang('admin::app.appearance.themes.manage.delete-confirm')",

                            agree: () => {
                                this.$axios.delete("{{ route('admin.appearance.themes.delete', 'THEME_CODE') }}".replace('THEME_CODE', theme.code))
                                    .then(response => {
                                        window.location.href = response.data.redirect_url;
                                    })
                                    .catch(error => this.flashError(error));
                            },
                        });
                    },

                    /**
                     * Flash the message a failed request answered with.
                     */
                    flashError(error) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: error.response?.data?.message ?? error.message,
                        });
                    },

                    /**
                     * Apply the theme to every selected channel.
                     */
                    activate() {
                        if (! this.channelIds.length) {
                            this.channelError = "@lang('validation.required', ['attribute' => trans('admin::app.appearance.themes.index.activate.channels')])";

                            return;
                        }

                        this.isLoading = true;

                        this.$axios.post("{{ route('admin.appearance.themes.activate', 'THEME_CODE') }}".replace('THEME_CODE', this.selected.code), {
                                channel_ids: this.channelIds,
                            })
                            .then(response => {
                                window.location.href = response.data.redirect_url;
                            })
                            .catch(error => {
                                this.isLoading = false;

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message ?? error.message,
                                });
                            });
                    },
                },
            });
        </script>

        <script
            type="text/x-template"
            id="v-appearance-theme-schedules-template"
        >
            <div
                class="box-shadow mt-8 rounded-sm bg-white p-4 dark:bg-gray-900"
                v-if="schedules.length || canSchedule"
            >
                <div class="flex items-start justify-between gap-4 max-sm:flex-wrap">
                    <div class="grid gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.appearance.themes.schedules.title')
                        </p>

                        <p class="text-xs text-gray-500 dark:text-gray-300">
                            @lang('admin::app.appearance.themes.schedules.info')
                        </p>
                    </div>

                    @if (bouncer()->hasPermission('appearance.themes.schedule'))
                        <button
                            type="button"
                            class="secondary-button"
                            @click="$refs.scheduleModal.open()"
                        >
                            @lang('admin::app.appearance.themes.schedules.create-btn')
                        </button>
                    @endif
                </div>

                <!-- Schedule List -->
                <div
                    class="mt-4 overflow-x-auto"
                    v-if="schedules.length"
                >
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300 rtl:text-right">
                        <thead class="text-xs font-semibold text-gray-800 dark:text-white">
                            <tr class="border-b dark:border-gray-800">
                                <th class="p-2">@lang('admin::app.appearance.themes.schedules.channel')</th>
                                <th class="p-2">@lang('admin::app.appearance.themes.schedules.theme')</th>
                                <th class="p-2">@lang('admin::app.appearance.themes.schedules.starts-at')</th>
                                <th class="p-2">@lang('admin::app.appearance.themes.schedules.ends-at')</th>
                                <th class="p-2">@lang('admin::app.appearance.themes.schedules.status')</th>
                                <th class="p-2"></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                class="border-b dark:border-gray-800"
                                v-for="schedule in schedules"
                                :key="schedule.id"
                            >
                                <td class="p-2" v-text="schedule.channel"></td>
                                <td class="p-2" v-text="schedule.theme"></td>
                                <td class="p-2" v-text="schedule.starts_at"></td>
                                <td class="p-2" v-text="schedule.ends_at"></td>
                                <td class="p-2" v-text="statuses[schedule.status]"></td>
                                <td class="p-2 text-right rtl:text-left">
                                    @if (bouncer()->hasPermission('appearance.themes.schedule'))
                                        <span
                                            class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                            title="@lang('admin::app.appearance.themes.schedules.delete-btn')"
                                            @click="remove(schedule)"
                                        ></span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p
                    class="mt-4 text-xs text-gray-400"
                    v-else
                >
                    @lang('admin::app.appearance.themes.schedules.empty')
                </p>

                @if (bouncer()->hasPermission('appearance.themes.schedule'))
                    <!-- Schedule Form -->
                    <Teleport to="body">
                        <x-admin::form
                            v-slot="{ meta, errors, handleSubmit }"
                            as="div"
                        >
                            <form @submit="handleSubmit($event, store)">
                                <x-admin::modal ref="scheduleModal">
                                    <x-slot:header>
                                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                                            @lang('admin::app.appearance.themes.schedules.create-btn')
                                        </p>
                                    </x-slot>

                                    <x-slot:content>
                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.appearance.themes.schedules.channel')
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="select"
                                                name="channel_id"
                                                rules="required"
                                                :label="trans('admin::app.appearance.themes.schedules.channel')"
                                            >
                                                <option
                                                    v-for="channel in channels"
                                                    :key="channel.id"
                                                    :value="channel.id"
                                                    v-text="channel.name"
                                                ></option>
                                            </x-admin::form.control-group.control>

                                            <x-admin::form.control-group.error control-name="channel_id" />
                                        </x-admin::form.control-group>

                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.appearance.themes.schedules.theme')
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="select"
                                                name="theme_code"
                                                rules="required"
                                                :label="trans('admin::app.appearance.themes.schedules.theme')"
                                            >
                                                <option
                                                    v-for="theme in themes"
                                                    :key="theme.code"
                                                    :value="theme.code"
                                                    v-text="theme.name"
                                                ></option>
                                            </x-admin::form.control-group.control>

                                            <x-admin::form.control-group.error control-name="theme_code" />
                                        </x-admin::form.control-group>

                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.appearance.themes.schedules.starts-at')
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="datetime"
                                                name="starts_at"
                                                rules="required"
                                                :label="trans('admin::app.appearance.themes.schedules.starts-at')"
                                            />

                                            <x-admin::form.control-group.error control-name="starts_at" />
                                        </x-admin::form.control-group>

                                        <x-admin::form.control-group class="mb-0!">
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.appearance.themes.schedules.ends-at')
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="datetime"
                                                name="ends_at"
                                                rules="required"
                                                :label="trans('admin::app.appearance.themes.schedules.ends-at')"
                                            />

                                            <x-admin::form.control-group.error control-name="ends_at" />
                                        </x-admin::form.control-group>
                                    </x-slot>

                                    <x-slot:footer>
                                        <x-admin::button
                                            button-type="submit"
                                            class="primary-button"
                                            :title="trans('admin::app.appearance.themes.schedules.save-btn')"
                                            ::loading="isLoading"
                                            ::disabled="isLoading"
                                        />
                                    </x-slot>
                                </x-admin::modal>
                            </form>
                        </x-admin::form>
                    </Teleport>
                @endif
            </div>
        </script>

        <script type="module">
            app.component('v-appearance-theme-schedules', {
                template: '#v-appearance-theme-schedules-template',

                props: ['schedules', 'themes', 'channels'],

                data() {
                    return {
                        isLoading: false,

                        canSchedule: @json(bouncer()->hasPermission('appearance.themes.schedule')),

                        statuses: {
                            pending: "@lang('admin::app.appearance.themes.schedules.pending')",
                            running: "@lang('admin::app.appearance.themes.schedules.running')",
                            finished: "@lang('admin::app.appearance.themes.schedules.finished')",
                        },
                    };
                },

                methods: {
                    /**
                     * Save a scheduled switch, reloading the gallery to list it.
                     */
                    store(params, { setErrors }) {
                        this.isLoading = true;

                        this.$axios.post("{{ route('admin.appearance.themes.schedules.store') }}", params)
                            .then(response => {
                                window.location.href = response.data.redirect_url;
                            })
                            .catch(error => {
                                this.isLoading = false;

                                if (error.response?.status == 422) {
                                    setErrors(error.response.data.errors);

                                    return;
                                }

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message ?? error.message,
                                });
                            });
                    },

                    /**
                     * Delete a scheduled switch once the admin confirms.
                     */
                    remove(schedule) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.$axios.delete("{{ route('admin.appearance.themes.schedules.delete', 'SCHEDULE_ID') }}".replace('SCHEDULE_ID', schedule.id))
                                    .then(response => {
                                        window.location.href = response.data.redirect_url;
                                    })
                                    .catch(error => {
                                        this.$emitter.emit('add-flash', {
                                            type: 'error',
                                            message: error.response?.data?.message ?? error.message,
                                        });
                                    });
                            },
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
