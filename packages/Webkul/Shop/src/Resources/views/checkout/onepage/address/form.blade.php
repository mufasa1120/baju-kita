@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-address-form-template"
    >
        <div class="mt-2 max-md:mt-3">
            <x-shop::form.control-group class="hidden">
                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.id'"
                    ::value="address.id"
                />
            </x-shop::form.control-group>

            {{-- ════════════════════════════════════════════════════════════════
                 TOMBOL PILIH LOKASI DARI GOOGLE MAPS
                 Klik → buka peta → pilih lokasi → form terisi otomatis
                 ════════════════════════════════════════════════════════════════ --}}
            <div class="mb-5">
                <button
                    type="button"
                    class="flex w-full items-center justify-center gap-2.5 rounded-xl border-2 border-dashed border-navyBlue/40 bg-navyBlue/5 py-3.5 text-navyBlue transition-all hover:border-navyBlue hover:bg-navyBlue/10 active:scale-[.98] max-sm:py-3"
                    @click="pickFromMap"
                    :disabled="mapLoading"
                >
                    <template v-if="mapLoading">
                        {{-- Spinner --}}
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-sm font-medium">Membuka peta...</span>
                    </template>
                    <template v-else>
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm font-medium">Pilih Lokasi dari Google Maps</span>
                    </template>
                </button>

                {{-- Sukses: tampilkan setelah lokasi dipilih dari peta --}}
                <transition name="fade">
                    <div
                        v-if="mapFilled"
                        class="mt-2 flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-2 text-xs text-green-700"
                    >
                        <svg class="h-4 w-4 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Alamat berhasil diisi dari peta — periksa dan lengkapi data di bawah jika perlu.
                    </div>
                    <p v-else class="mt-1.5 text-center text-xs text-zinc-400">
                        Kota, provinsi, dan kode pos akan terisi otomatis
                    </p>
                </transition>
            </div>
            {{-- ════════════════════════════════════════════════════════════════ --}}

            <!-- Company Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label>
                    @lang('shop::app.checkout.onepage.address.company-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.company_name'"
                    ::value="address.company_name"
                    :placeholder="trans('shop::app.checkout.onepage.address.company-name')"
                />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.company_name.after') !!}

            <!-- First Name -->
            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.first-name')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.first_name'"
                        ::value="address.first_name"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.first-name')"
                        :placeholder="trans('shop::app.checkout.onepage.address.first-name')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.first_name'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.first_name.after') !!}

                <!-- Last Name -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.last-name')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.last_name'"
                        ::value="address.last_name"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.last-name')"
                        :placeholder="trans('shop::app.checkout.onepage.address.last-name')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.last_name'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.last_name.after') !!}
            </div>

            <!-- Email -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required !mt-0">
                    @lang('shop::app.checkout.onepage.address.email')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="email"
                    ::name="controlName + '.email'"
                    ::value="address.email"
                    rules="required|email"
                    :label="trans('shop::app.checkout.onepage.address.email')"
                    placeholder="email@example.com"
                />

                <x-shop::form.control-group.error ::name="controlName + '.email'" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.email.after') !!}

            <!-- Vat ID -->
            <template v-if="controlName=='billing'">
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        @lang('shop::app.checkout.onepage.address.vat-id')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.vat_id'"
                        ::value="address.vat_id"
                        :label="trans('shop::app.checkout.onepage.address.vat-id')"
                        :placeholder="trans('shop::app.checkout.onepage.address.vat-id')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.vat_id'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.vat_id.after') !!}
            </template>

            <!-- Street Address -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required !mt-0">
                    @lang('shop::app.checkout.onepage.address.street-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.address.[0]'"
                    ::value="address.address[0]"
                    rules="required|address"
                    :label="trans('shop::app.checkout.onepage.address.street-address')"
                    :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
                />

                <x-shop::form.control-group.error
                    class="mb-2"
                    ::name="controlName + '.address.[0]'"
                />

                @if (core()->getConfigData('customer.address.information.street_lines') > 1)
                    @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
                        <x-shop::form.control-group.control
                            type="text"
                            ::name="controlName + '.address.[{{ $i }}]'"
                            rules="address"
                            :label="trans('shop::app.checkout.onepage.address.street-address')"
                            :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
                        />

                        <x-shop::form.control-group.error
                            class="mb-2"
                            ::name="controlName + '.address.[{{ $i }}]'"
                        />
                    @endfor
                @endif
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.address.after') !!}

            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <!-- Country -->
                <x-shop::form.control-group class="!mb-4">
                    <x-shop::form.control-group.label class="{{ core()->isCountryRequired() ? 'required' : '' }} !mt-0">
                        @lang('shop::app.checkout.onepage.address.country')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        ::name="controlName + '.country'"
                        ::value="address.country"
                        v-model="selectedCountry"
                        rules="{{ core()->isCountryRequired() ? 'required' : '' }}"
                        :label="trans('shop::app.checkout.onepage.address.country')"
                        :placeholder="trans('shop::app.checkout.onepage.address.country')"
                    >
                        <option value="">
                            @lang('shop::app.checkout.onepage.address.select-country')
                        </option>

                        <option
                            v-for="country in countries"
                            :value="country.code"
                        >
                            @{{ country.name }}
                        </option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="controlName + '.country'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.country.after') !!}

                <!-- State -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="{{ core()->isStateRequired() ? 'required' : '' }} !mt-0">
                        @lang('shop::app.checkout.onepage.address.state')
                    </x-shop::form.control-group.label>

                    <template v-if="states">
                        <template v-if="haveStates">
                            <x-shop::form.control-group.control
                                type="select"
                                ::name="controlName + '.state'"
                                rules="{{ core()->isStateRequired() ? 'required' : '' }}"
                                ::value="address.state"
                                :label="trans('shop::app.checkout.onepage.address.state')"
                                :placeholder="trans('shop::app.checkout.onepage.address.state')"
                            >
                                <option value="">
                                    @lang('shop::app.checkout.onepage.address.select-state')
                                </option>

                                <option
                                    v-for='(state, index) in states[selectedCountry]'
                                    :value="state.code"
                                >
                                    @{{ state.default_name }}
                                </option>
                            </x-shop::form.control-group.control>
                        </template>

                        <template v-else>
                            <x-shop::form.control-group.control
                                type="text"
                                ::name="controlName + '.state'"
                                ::value="address.state"
                                rules="{{ core()->isStateRequired() ? 'required' : '' }}"
                                :label="trans('shop::app.checkout.onepage.address.state')"
                                :placeholder="trans('shop::app.checkout.onepage.address.state')"
                            />
                        </template>
                    </template>

                    <x-shop::form.control-group.error ::name="controlName + '.state'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.state.after') !!}
            </div>

            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <!-- City -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.city')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.city'"
                        ::value="address.city"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.city')"
                        :placeholder="trans('shop::app.checkout.onepage.address.city')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.city'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.city.after') !!}

                <!-- Postcode -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="{{ core()->isPostCodeRequired() ? 'required' : '' }} !mt-0">
                        @lang('shop::app.checkout.onepage.address.postcode')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.postcode'"
                        ::value="address.postcode"
                        rules="{{ core()->isPostCodeRequired() ? 'required' : '' }}|postcode"
                        :label="trans('shop::app.checkout.onepage.address.postcode')"
                        :placeholder="trans('shop::app.checkout.onepage.address.postcode')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.postcode'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.postcode.after') !!}
            </div>

            <!-- Phone Number -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required !mt-0">
                    @lang('shop::app.checkout.onepage.address.telephone')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.phone'"
                    ::value="address.phone"
                    rules="required|phone"
                    :label="trans('shop::app.checkout.onepage.address.telephone')"
                    :placeholder="trans('shop::app.checkout.onepage.address.telephone')"
                />

                <x-shop::form.control-group.error ::name="controlName + '.phone'" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.phone.after') !!}
        </div>
    </script>

    <script type="module">
        app.component('v-checkout-address-form', {
            template: '#v-checkout-address-form-template',

            props: {
                controlName: {
                    type: String,
                    required: true,
                },

                address: {
                    type: Object,

                    default: () => ({
                        id: 0,
                        company_name: '',
                        first_name: '',
                        last_name: '',
                        email: '',
                        address: [],
                        country: '',
                        state: '',
                        city: '',
                        postcode: '',
                        phone: '',
                    }),
                },
            },

            data() {
                return {
                    selectedCountry: this.address.country,

                    countries: [],

                    states: null,

                    /* ── Google Maps tambahan ── */
                    mapFilled : false,   // true setelah lokasi dipilih dari peta
                    mapLoading: false,   // true saat modal sedang terbuka
                }
            },

            computed: {
                haveStates() {
                    return !! this.states[this.selectedCountry]?.length;
                },
            },

            mounted() {
                this.getCountries();
                this.getStates();
            },

            methods: {
                getCountries() {
                    this.$axios.get("{{ route('shop.api.core.countries') }}")
                        .then(response => {
                            this.countries = response.data.data;
                        })
                        .catch(() => {});
                },

                getStates() {
                    this.$axios.get("{{ route('shop.api.core.states') }}")
                        .then(response => {
                            this.states = response.data.data;
                        })
                        .catch(() => {});
                },

                /* ════════════════════════════════════════════════════════════
                 *  GOOGLE MAPS INTEGRATION
                 * ════════════════════════════════════════════════════════════ */

                /**
                 * Dipanggil saat tombol "Pilih Lokasi dari Google Maps" diklik.
                 * Membuka modal peta dan menyerahkan fillFromMap sebagai callback.
                 */
                pickFromMap() {
                    if (typeof window.openGoogleMapPicker !== 'function') {
                        alert('Google Maps belum siap. Coba beberapa saat lagi.');
                        return;
                    }
                    this.mapLoading = true;

                    // openGoogleMapPicker menerima callback(place) yang dipanggil
                    // setelah pengguna menekan "Konfirmasi Lokasi"
                    window.openGoogleMapPicker((place) => {
                        this.mapLoading = false;
                        this.fillFromMap(place);
                    });

                    // Jaga-jaga jika user menutup modal tanpa konfirmasi
                    const unwatch = setInterval(() => {
                        const modal = document.getElementById('gmap-modal');
                        if (modal && modal.style.display === 'none') {
                            this.mapLoading = false;
                            clearInterval(unwatch);
                        }
                    }, 300);
                },

                /**
                 * Mengisi field-field form dari data lokasi yang dikembalikan Maps.
                 * Urutan: country dulu (Vue reaktif → trigger load state),
                 * lalu field teks, lalu state setelah opsi tersedia.
                 *
                 * @param {object} place  { address, city, state, stateShort, postcode, country, formatted }
                 */
                fillFromMap(place) {
                    if (!place) return;

                    /* 1. Country — update via Vue reaktif (v-model="selectedCountry") */
                    if (place.country) {
                        this.selectedCountry = place.country;
                    }

                    /* 2. Isi field teks (street, city, postcode) setelah DOM diperbarui */
                    this.$nextTick(() => {
                        const prefix = this.controlName;

                        if (place.address)  this.setFormField(`${prefix}.address.[0]`, place.address);
                        if (place.city)     this.setFormField(`${prefix}.city`,         place.city);
                        if (place.postcode) this.setFormField(`${prefix}.postcode`,     place.postcode);

                        /* 3. Country select — pastikan VeeValidate tahu ada perubahan */
                        if (place.country) {
                            this.setFormSelect(`${prefix}.country`, place.country);
                        }

                        /* 4. State — tunggu hingga opsi provinsi selesai dirender
                         *    (Bagisto fetch state list setelah country berubah)        */
                        if (place.state || place.stateShort) {
                            this.fillStateWithRetry(prefix, place.state, place.stateShort, 5);
                        }

                        this.mapFilled = true;
                    });
                },

                /**
                 * Coba isi field state dengan retry (karena opsi mungkin belum
                 * selesai dirender saat country baru saja diganti).
                 *
                 * @param {string} prefix      controlName ('billing'/'shipping')
                 * @param {string} stateLong   Nama provinsi panjang, mis. "Jawa Barat"
                 * @param {string} stateShort  Kode provinsi pendek, mis. "JB"
                 * @param {number} attempts    Berapa kali lagi boleh dicoba
                 */
                fillStateWithRetry(prefix, stateLong, stateShort, attempts) {
                    if (attempts <= 0) return;

                    const filled = this.setFormSelect(`${prefix}.state`, stateLong, stateShort)
                                || this.setFormField(`${prefix}.state`, stateLong, true /* silent */);

                    if (!filled) {
                        setTimeout(() => {
                            this.fillStateWithRetry(prefix, stateLong, stateShort, attempts - 1);
                        }, 400);
                    }
                },

                /**
                 * Isi input[type=text] secara programatik dan notifikasi VeeValidate.
                 * Menggunakan native HTMLInputElement setter agar Vue tidak memblokir.
                 *
                 * @param  {string}  name    Nilai atribut `name` pada input
                 * @param  {string}  value   Nilai yang akan diisi
                 * @param  {boolean} silent  Jika true, tidak tampilkan error jika elemen tidak ada
                 * @return {boolean}         true jika berhasil
                 */
                setFormField(name, value, silent = false) {
                    if (!value) return false;

                    const el = document.querySelector(`input[name="${name}"], textarea[name="${name}"]`);
                    if (!el) {
                        if (!silent) console.warn(`[gmaps] Field tidak ditemukan: ${name}`);
                        return false;
                    }

                    /* Gunakan native value setter — bypass Vue's reactive override */
                    const nativeSetter = Object.getOwnPropertyDescriptor(
                        window.HTMLInputElement.prototype, 'value'
                    )?.set;

                    if (nativeSetter) {
                        nativeSetter.call(el, value);
                    } else {
                        el.value = value;
                    }

                    /* Dispatch events agar VeeValidate memperbarui nilai internalnya */
                    el.dispatchEvent(new Event('input',  { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));

                    return true;
                },

                /**
                 * Isi <select> secara programatik: coba exact match dulu,
                 * lalu fuzzy match pada teks opsi (untuk nama provinsi Indonesia).
                 *
                 * @param  {string} name        Nilai atribut `name` pada select
                 * @param  {string} value       Nilai yang dicari (long name / kode)
                 * @param  {string} [fallback]  Nilai alternatif jika value tidak ditemukan
                 * @return {boolean}            true jika opsi berhasil dipilih
                 */
                setFormSelect(name, value, fallback = '') {
                    if (!value) return false;

                    const el = document.querySelector(`select[name="${name}"]`);
                    if (!el) return false;

                    const tryMatch = (needle) => {
                        if (!needle) return null;
                        const lower = needle.toLowerCase();

                        /* 1. Exact match pada value */
                        for (const opt of el.options) {
                            if (opt.value.toLowerCase() === lower) return opt.value;
                        }

                        /* 2. Exact match pada text */
                        for (const opt of el.options) {
                            if (opt.text.toLowerCase() === lower) return opt.value;
                        }

                        /* 3. Fuzzy: option text mengandung needle atau sebaliknya */
                        for (const opt of el.options) {
                            const t = opt.text.toLowerCase();
                            if (t.includes(lower) || lower.includes(t)) return opt.value;
                        }

                        return null;
                    };

                    const matched = tryMatch(value) || tryMatch(fallback);
                    if (!matched) return false;

                    el.value = matched;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    el.dispatchEvent(new Event('input',  { bubbles: true }));

                    /* Sinkronisasi v-model selectedCountry jika ini field country */
                    if (name.endsWith('.country')) {
                        this.selectedCountry = matched;
                    }

                    return true;
                },
                /* ════════════════════════════════════════════════════════════ */
            }
        });
    </script>
@endPushOnce