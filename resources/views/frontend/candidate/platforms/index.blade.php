@extends('frontend.layouts.app')

@section('title', 'My Platforms')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">
        My Platforms
    </h1>
    <p class="mt-1 text-sm text-hb-muted">
        Manage your professional external links and integrated platform accounts.
    </p>
@endsection

@section('content')

<div x-data="{
    openModal: false,
    isEdit: false,
    editIndex: null,
    platformDropdownOpen: false,

    form: {
        id: '',
        selectedPlatform: 'LinkedIn',
        customName: '',
        url: ''
    },

    platforms: [],

    platformOptions: [
        { name: 'Facebook', icon: '📘' },
        { name: 'WhatsApp', icon: '🟢' },
        { name: 'LinkedIn', icon: '💼' },
        { name: 'GitHub', icon: '💻' },
        { name: 'Discord', icon: '🎮' },
        { name: 'Other', icon: '🔗' }
    ],

    get selectedOption() {
        return this.platformOptions.find(
            p => p.name === this.form.selectedPlatform
        ) || this.platformOptions[5];
    },

    resetForm() {
        this.form = {
            id: '',
            selectedPlatform: 'LinkedIn',
            customName: '',
            url: ''
        };

        this.isEdit = false;
        this.editIndex = null;
        this.platformDropdownOpen = false;
    },

    addPlatform() {
        this.resetForm();
        this.openModal = true;
    },

    selectPlatform(platform) {
        this.form.selectedPlatform = platform.name;
        this.platformDropdownOpen = false;
    },

    editPlatform(index) {
        const item = this.platforms[index];

        this.isEdit = true;
        this.editIndex = index;

        const isStandardPlatform = this.platformOptions.some(
            p => p.name === item.name && p.name !== 'Other'
        );

        this.form = {
            id: item.id,
            selectedPlatform: isStandardPlatform ? item.name : 'Other',
            customName: isStandardPlatform ? '' : item.name,
            url: item.url
        };

        this.openModal = true;
    },

    saveModal() {
        let name = this.form.selectedPlatform === 'Other'
            ? this.form.customName.trim()
            : this.form.selectedPlatform;

        let url = this.form.url.trim();

        if (!name || !url) {
            return;
        }

        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'https://' + url;
        }

        let selectedIcon = this.form.selectedPlatform === 'Other'
            ? '🔗'
            : this.selectedOption.icon;

        if (this.isEdit) {
            this.platforms[this.editIndex] = {
                id: this.form.id,
                name: name,
                url: url,
                icon: selectedIcon
            };

            this.platforms = [...this.platforms];

        } else {
            let newId = this.platforms.length > 0
                ? Math.max(...this.platforms.map(p => p.id)) + 1
                : 1;

            this.platforms.push({
                id: newId,
                name: name,
                url: url,
                icon: selectedIcon
            });
        }

        this.openModal = false;
        this.resetForm();
    },

    deletePlatform(index) {
        if (confirm('Are you sure you want to delete this platform?')) {
            this.platforms.splice(index, 1);
        }
    },

    closeModal() {
        this.openModal = false;
        this.resetForm();
    }

}" class="space-y-6">

    {{-- Main Platforms Card --}}
    <x-card>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">

            <div>
                <h3 class="text-lg font-bold text-hb-navy">
                    Connected Platforms
                </h3>

                <p class="text-sm text-hb-muted">
                    Add and manage links to your professional profiles.
                </p>
            </div>

            <button
                @click="addPlatform()"
                type="button"
                class="inline-flex items-center justify-center px-5 py-2.5
                       bg-blue-600 border border-transparent rounded-lg
                       font-semibold text-xs text-white uppercase tracking-widest
                       hover:bg-blue-700 active:bg-blue-800 transition shadow-md"
            >
                + Add Platform
            </button>

        </div>


        {{-- Table View (تم تحديد عرض الأعمدة بوضوح لضمان التناسق) --}}
        <template x-if="platforms.length > 0">

            <div class="overflow-x-auto rounded-lg border border-gray-200 mb-6 bg-white">

                <table class="w-full divide-y divide-gray-200 text-sm table-fixed">

                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider text-left">
                        <tr>
                            <th class="w-16 px-4 py-3">ID</th>
                            <th class="w-20 px-4 py-3">Icon</th>
                            <th class="w-48 px-4 py-3">Platform Name</th>
                            <th class="px-4 py-3">URL</th>
                            <th class="w-36 px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">

                        <template x-for="(item, index) in platforms" :key="item.id">

                            <tr class="hover:bg-gray-50 transition-colors">

                                {{-- ID --}}
                                <td class="px-4 py-3 font-medium text-gray-500 truncate"
                                    x-text="item.id">
                                </td>

                                {{-- Icon --}}
                                <td class="px-4 py-3 text-xl truncate"
                                    x-text="item.icon">
                                </td>

                                {{-- Platform Name --}}
                                <td class="px-4 py-3 font-bold text-hb-navy truncate"
                                    x-text="item.name">
                                </td>

                                {{-- URL --}}
                                <td class="px-4 py-3 truncate">
                                    <a
                                        :href="item.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 hover:underline text-xs font-medium block truncate"
                                        :title="item.url"
                                        x-text="item.url"
                                    ></a>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">

                                    <button
                                        @click="editPlatform(index)"
                                        type="button"
                                        class="px-3 py-1 text-xs font-medium text-blue-600
                                               bg-blue-50 rounded-md hover:bg-blue-100 transition"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        @click="deletePlatform(index)"
                                        type="button"
                                        class="px-3 py-1 text-xs font-medium text-red-600
                                               bg-red-50 rounded-md hover:bg-red-100 transition"
                                    >
                                        Delete
                                    </button>

                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>

        </template>


        {{-- Empty State --}}
        <template x-if="platforms.length === 0">

            <div class="py-12 text-center mb-6">

                <div class="inline-flex items-center justify-center w-16 h-16
                            mb-4 rounded-full bg-blue-50 text-blue-600
                            text-2xl shadow-inner">
                    🌐
                </div>

                <h3 class="text-lg font-bold text-hb-navy mb-1">
                    No platforms added yet
                </h3>

                <p class="text-sm text-hb-muted max-w-sm mx-auto">
                    Connecting your profiles helps recruiters evaluate your
                    work and technical presence online.
                </p>

            </div>

        </template>


        {{-- Global Save Button --}}
        <div class="flex justify-end">

            <form
                action="{{ route('frontend.candidate.profile.update.submit') }}"
                method="POST"
            >

                @csrf

                <button
                    type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700
                           text-white rounded-lg font-semibold text-sm
                           transition shadow-md"
                >
                    Save Changes
                </button>

            </form>

        </div>

    </x-card>


    {{-- Add / Edit Platform Modal --}}
    <div
        x-show="openModal"
        x-cloak
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="closeModal()"
    >

        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-gray-900/40"
            @click="closeModal()"
        ></div>


        {{-- Modal Wrapper --}}
        <div class="flex min-h-full items-center justify-center px-4 py-8">

            {{-- Modal Content --}}
            <div
                class="relative w-full max-w-md bg-white rounded-lg
                       shadow-xl border border-gray-300 overflow-visible"
                @click.stop
            >

                {{-- Modal Header --}}
                <div class="flex items-center justify-between
                            px-5 py-3 border-b border-gray-200">

                    <h3
                        class="text-sm font-semibold text-gray-900"
                        x-text="isEdit ? 'Edit Platform' : 'Create New Platform'"
                    ></h3>

                    <button
                        @click="closeModal()"
                        type="button"
                        class="text-gray-400 hover:text-gray-700
                               text-lg font-bold leading-none"
                    >
                        &times;
                    </button>

                </div>


                {{-- Modal Form --}}
                <form @submit.prevent="saveModal()">

                    {{-- Modal Body --}}
                    <div class="px-5 py-4 space-y-4">

                        {{-- Platform Name --}}
                        <div>

                            <label class="block text-xs font-semibold
                                          text-gray-700 mb-1.5">
                                Platform Name *
                            </label>

                            <div class="relative">

                                {{-- Selected Platform --}}
                                <button
                                    type="button"
                                    @click="platformDropdownOpen = !platformDropdownOpen"
                                    class="w-full h-10 px-3 flex items-center
                                           justify-between bg-gray-50
                                           border border-gray-200 rounded-md
                                           text-sm text-gray-700
                                           hover:border-gray-300
                                           focus:border-blue-500
                                           focus:ring-1 focus:ring-blue-500"
                                >

                                    <div class="flex items-center gap-3">
                                        <span
                                            class="text-base"
                                            x-text="selectedOption.icon"
                                        ></span>

                                        <span x-text="form.selectedPlatform"></span>
                                    </div>

                                    <svg
                                        class="w-4 h-4 text-gray-400 transition-transform"
                                        :class="platformDropdownOpen ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>

                                </button>


                                {{-- Dropdown Options --}}
                                <div
                                    x-show="platformDropdownOpen"
                                    x-transition
                                    @click.outside="platformDropdownOpen = false"
                                    style="display: none;"
                                    class="absolute z-50 mt-1 w-full
                                           bg-white border border-gray-200
                                           rounded-md shadow-lg overflow-hidden"
                                >

                                    <template
                                        x-for="platform in platformOptions"
                                        :key="platform.name"
                                    >

                                        <button
                                            type="button"
                                            @click="selectPlatform(platform)"
                                            class="w-full flex items-center gap-3
                                                   px-3 py-2.5 text-sm
                                                   text-gray-700 hover:bg-gray-50
                                                   transition"
                                            :class="form.selectedPlatform === platform.name
                                                ? 'bg-blue-50 text-blue-600'
                                                : ''"
                                        >
                                            <span
                                                class="text-base"
                                                x-text="platform.icon"
                                            ></span>

                                            <span x-text="platform.name"></span>

                                            <svg
                                                x-show="form.selectedPlatform === platform.name"
                                                class="w-4 h-4 ml-auto"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>

                                        </button>

                                    </template>

                                </div>

                            </div>

                        </div>


                        {{-- Custom Name for Other --}}
                        <div
                            x-show="form.selectedPlatform === 'Other'"
                            x-transition
                            style="display: none;"
                        >

                            <label class="block text-xs font-semibold
                                          text-gray-700 mb-1.5">
                                Custom Platform Name *
                            </label>

                            <input
                                x-model="form.customName"
                                type="text"
                                placeholder="Enter platform name"
                                :required="form.selectedPlatform === 'Other'"
                                class="w-full h-10 px-3 bg-gray-50
                                       border border-gray-200 rounded-md
                                       text-sm text-gray-700
                                       placeholder-gray-400
                                       focus:bg-white focus:border-blue-500
                                       focus:ring-1 focus:ring-blue-500"
                            >

                        </div>


                        {{-- Profile URL --}}
                        <div>

                            <label class="block text-xs font-semibold
                                          text-gray-700 mb-1.5">
                                Profile URL *
                            </label>

                            <input
                                x-model="form.url"
                                type="url"
                                placeholder="https://..."
                                required
                                class="w-full h-10 px-3 bg-gray-50
                                       border border-gray-200 rounded-md
                                       text-sm text-gray-700
                                       placeholder-gray-400
                                       focus:bg-white focus:border-blue-500
                                       focus:ring-1 focus:ring-blue-500"
                            >

                        </div>

                    </div>


                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end
                                gap-2 px-5 py-3
                                border-t border-gray-200
                                bg-gray-50 rounded-b-lg">

                        {{-- Cancel --}}
                        <button
                            type="button"
                            @click="closeModal()"
                            class="px-4 py-2 text-xs font-medium
                                   text-gray-700 bg-white
                                   border border-gray-300 rounded-md
                                   hover:bg-gray-100 transition"
                        >
                            Cancel
                        </button>


                        {{-- Add Platform / Update Platform --}}
                        <button
                            type="submit"
                            class="px-4 py-2 text-xs font-semibold
                                   text-white bg-blue-600
                                   rounded-md hover:bg-blue-700
                                   transition shadow-sm"
                            x-text="isEdit ? 'Update Platform' : 'Add Platform'"
                        ></button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection