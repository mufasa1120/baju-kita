{!! view_render_event('bagisto.shop.checkout.onepage.address.customer.before') !!}

<!-- Customer Address Vue Component -->
<v-checkout-address-customer
    :cart="cart"
    @processing="stepForward"
    @processed="stepProcessed"
>
    <!-- Billing Address Shimmer -->
    <x-shop::shimmer.checkout.onepage.address />
</v-checkout-address-customer>

{!! view_render_event('bagisto.shop.checkout.onepage.address.customer.after') !!}

{{-- ════════════════════════════════════════════════════════════════
     GOOGLE MAPS MODAL HTML
     Ditempatkan di luar @pushOnce agar masuk ke dalam <body>
     ════════════════════════════════════════════════════════════════ --}}
<div
    id="gmap-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="gmap-modal-title"
    style="
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(0,0,0,0.55);
        align-items: center;
        justify-content: center;
    "
>
    <div style="
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 16px;
        width: min(95vw, 700px);
        height: min(90vh, 620px);
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0,0,0,0.28);
    ">

        {{-- ── Header ── --}}
        <div style="
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            flex-shrink: 0;
        ">
            <h2 id="gmap-modal-title" style="font-size:15px; font-weight:600; color:#111827; margin:0; white-space:nowrap;">
                📍 Pilih Lokasi
            </h2>

            {{-- Search input --}}
            <div style="position:relative; flex:1;">
                <svg
                    style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#9ca3af; pointer-events:none;"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    id="gmap-search-input"
                    type="text"
                    placeholder="Cari alamat, jalan, atau kota..."
                    autocomplete="off"
                    style="
                        width: 100%;
                        padding: 9px 12px 9px 34px;
                        border: 1.5px solid #d1d5db;
                        border-radius: 8px;
                        font-size: 13px;
                        outline: none;
                        box-sizing: border-box;
                        transition: border-color .15s, box-shadow .15s;
                    "
                    onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,.18)';"
                    onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                />
            </div>

            {{-- Close button --}}
            <button
                id="gmap-close"
                type="button"
                aria-label="Tutup"
                style="
                    width: 32px; height: 32px; flex-shrink: 0;
                    border: none; background: #f3f4f6; border-radius: 50%;
                    cursor: pointer; font-size: 15px; line-height: 1;
                    display: flex; align-items: center; justify-content: center;
                    transition: background .15s;
                "
                onmouseover="this.style.background='#e5e7eb';"
                onmouseout="this.style.background='#f3f4f6';"
            >✕</button>
        </div>

        {{-- ── Map canvas ── --}}
        <div id="gmap-canvas" style="flex:1; min-height:0;"></div>

        {{-- ── Footer ── --}}
        <div style="
            padding: 12px 16px 14px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            flex-shrink: 0;
        ">
            {{-- Selected address preview --}}
            <div style="display:flex; align-items:flex-start; gap:8px; margin-bottom:12px; min-height:36px;">
                <svg style="width:16px; height:16px; flex-shrink:0; margin-top:2px; color:#6b7280;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p id="gmap-selected-addr" style="
                    font-size: 13px;
                    color: #374151;
                    line-height: 1.45;
                    margin: 0;
                ">
                    Klik peta atau gunakan kolom pencarian untuk memilih lokasi
                </p>
            </div>

            {{-- Confirm button --}}
            <button
                id="gmap-confirm-btn"
                type="button"
                style="
                    width: 100%;
                    padding: 12px 16px;
                    background: #0041d9;
                    color: #fff;
                    border: none;
                    border-radius: 10px;
                    font-size: 14px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background .15s, transform .1s;
                "
                onmouseover="this.style.background='#0036b8';"
                onmouseout="this.style.background='#0041d9';"
                onmousedown="this.style.transform='scale(.98)';"
                onmouseup="this.style.transform='scale(1)';"
            >
                ✓ &nbsp;Konfirmasi Lokasi Ini
            </button>
        </div>
    </div>
</div>
{{-- ════════════════════════════════════════════════════════════════ --}}

@pushOnce('scripts')

    <script>
    (function () {
        // ── State ──────────────────────────────────────────────
        var _cb       = null;
        var _ready    = false;
        var _map      = null;
        var _marker   = null;
        var _geocoder = null;
        var _ac       = null;
        var _place    = null;
        var _pending  = null;

        // ── Load Google Maps script ────────────────────────────
        var s = document.createElement('script');
        s.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDXbmsnN9ezbAScphNgcMnpMEez5r0gHZI&libraries=places';
        s.async = true;
        s.defer = true;
        s.onload = function () {
            _ready = true;
            if (_pending) { _pending(); _pending = null; }
        };
        document.head.appendChild(s);

        // ── Public API — dipanggil dari v-checkout-address-form ──
        window.openGoogleMapPicker = function (callback) {
            _cb    = callback;
            _place = null;

            var modal = document.getElementById('gmap-modal');
            var addr  = document.getElementById('gmap-selected-addr');
            var inp   = document.getElementById('gmap-search-input');

            // Tampilkan modal (ganti classList.add('active') dengan style.display)
            modal.style.display = 'flex';
            addr.textContent = 'Klik peta atau gunakan kolom pencarian untuk memilih lokasi';
            inp.value = '';

            // Tunggu modal visible agar canvas punya dimensi
            setTimeout(function () {
                if (_ready && !_map)  { initMap(); }
                else if (_map)        { google.maps.event.trigger(_map, 'resize'); if (_marker.getPosition()) _map.panTo(_marker.getPosition()); }
                else                  { _pending = initMap; }
            }, 120);
        };

        // ── Init map ───────────────────────────────────────────
        function initMap() {
            var canvas = document.getElementById('gmap-canvas');
            if (!canvas || canvas.offsetHeight === 0) { setTimeout(initMap, 100); return; }

            // Default center: Jakarta
            var center = { lat: -6.2088, lng: 106.8456 };

            _map = new google.maps.Map(canvas, {
                center             : center,
                zoom               : 13,
                mapTypeControl     : false,
                streetViewControl  : false,
                fullscreenControl  : false,
                gestureHandling    : 'cooperative',
            });

            _geocoder = new google.maps.Geocoder();

            _marker = new google.maps.Marker({
                map      : _map,
                draggable: true,
                visible  : false,
                animation: google.maps.Animation.DROP,
            });

            // Force resize sekali lagi supaya tiles muncul
            setTimeout(function () {
                google.maps.event.trigger(_map, 'resize');
                _map.setCenter(center);
            }, 150);

            // Gunakan lokasi browser kalau tersedia
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (p) {
                    var loc = { lat: p.coords.latitude, lng: p.coords.longitude };
                    _map.setCenter(loc);
                    _map.setZoom(15);
                }, function () {});
            }

            _map.addListener('click',       function (e) { pinAndGeocode(e.latLng); });
            _marker.addListener('dragend',  function ()  { pinAndGeocode(_marker.getPosition()); });

            // Autocomplete hanya untuk Indonesia
            _ac = new google.maps.places.Autocomplete(
                document.getElementById('gmap-search-input'),
                {
                    componentRestrictions: { country: 'id' },
                    fields: ['geometry', 'formatted_address', 'address_components'],
                }
            );

            _ac.addListener('place_changed', function () {
                var p = _ac.getPlace();
                if (!p.geometry) return;
                _map.setCenter(p.geometry.location);
                _map.setZoom(17);
                _marker.setPosition(p.geometry.location);
                _marker.setVisible(true);
                parsePlace(p);
            });
        }

        function pinAndGeocode(latLng) {
            _marker.setPosition(latLng);
            _marker.setVisible(true);
            _geocoder.geocode({ location: latLng }, function (res, status) {
                if (status === 'OK' && res[0]) parsePlace(res[0]);
            });
        }

        function parsePlace(p) {
            var comps = p.address_components || [];

            /* Helper: ambil long_name / short_name berdasarkan tipe komponen */
            var get  = function (types) {
                var c = comps.find(function (c) {
                    return types.some(function (t) { return c.types.includes(t); });
                });
                return c ? c.long_name  : '';
            };
            var gets = function (types) {
                var c = comps.find(function (c) {
                    return types.some(function (t) { return c.types.includes(t); });
                });
                return c ? c.short_name : '';
            };

            // Jalan: gabungkan nomor + nama jalan, fallback ke kelurahan / formatted
            var street = [get(['street_number']), get(['route'])].filter(Boolean).join(' ')
                || get(['administrative_area_level_4', 'administrative_area_level_5', 'sublocality_level_2'])
                || p.formatted_address
                || '';

            // Kota/Kabupaten dari level_2 (ID: Kota/Kabupaten), fallback ke locality
            var city = get(['administrative_area_level_2', 'locality'])
                .replace(/^(Kota|Kabupaten)\s+/i, ''); // buang prefix Kota/Kabupaten

            _place = {
                address   : street,
                city      : city,
                state     : get(['administrative_area_level_1']),   // Provinsi (long)
                stateShort: gets(['administrative_area_level_1']),  // Provinsi (short)
                postcode  : get(['postal_code']),
                country   : gets(['country']),   // "ID"
                formatted : p.formatted_address || street,
            };

            document.getElementById('gmap-selected-addr').textContent = '📍 ' + _place.formatted;
        }

        // ── Modal controls ─────────────────────────────────────
        function setupModal() {
            var modal      = document.getElementById('gmap-modal');
            var closeBtn   = document.getElementById('gmap-close');
            var confirmBtn = document.getElementById('gmap-confirm-btn');

            if (!modal || !closeBtn || !confirmBtn) { setTimeout(setupModal, 100); return; }

            // Sembunyikan modal (ganti classList.remove('active'))
            var close = function () { modal.style.display = 'none'; };

            closeBtn.addEventListener('click', close);

            // Klik di luar dialog juga menutup
            modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

            // Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') close();
            });

            confirmBtn.addEventListener('click', function () {
                if (!_place) {
                    alert('Silakan pilih lokasi di peta atau gunakan kolom pencarian terlebih dahulu.');
                    return;
                }
                if (typeof _cb === 'function') _cb(_place);
                close();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupModal);
        } else {
            setupModal();
        }
    })();
    </script>

    <script
        type="text/x-template"
        id="v-checkout-address-customer-template"
    >
        <template v-if="isLoading">
            <!-- Billing Address Shimmer -->
            <x-shop::shimmer.checkout.onepage.address />
        </template>

        <template v-else>
            <!-- Saved Addresses -->
            <template v-if="! activeAddressForm && customerSavedAddresses.billing.length">
                <x-shop::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, addAddressToCart)">
                        <!-- Billing Address Header -->
                        <div class="mb-4 flex items-center justify-between max-md:mb-2">
                            <h2 class="text-xl font-medium max-sm:text-base max-sm:font-normal">
                                @lang('shop::app.checkout.onepage.address.billing-address')
                            </h2>
                        </div>

                        <!-- Saved Customer Addresses Cards -->
                        <div class="mb-2 grid grid-cols-2 gap-5 max-1060:grid-cols-[1fr] max-lg:grid-cols-2 max-md:mt-2 max-md:grid-cols-1">
                            <div
                                class="relative max-w-[414px] cursor-pointer select-none rounded-xl border border-zinc-200 p-0 max-md:flex-wrap max-md:rounded-lg"
                                v-for="address in customerSavedAddresses.billing"
                            >
                                <!-- Actions -->
                                <div class="absolute top-5 flex gap-2 ltr:right-5 rtl:left-5">
                                    <x-shop::form.control-group class="!mb-0 flex items-center gap-2.5">
                                        <x-shop::form.control-group.control
                                            type="radio"
                                            name="billing.id"
                                            ::id="`billing_address_id_${address.id}`"
                                            ::for="`billing_address_id_${address.id}`"
                                            ::value="address.id"
                                            v-model="selectedAddresses.billing_address_id"
                                            rules="required"
                                            label="{{ trans('shop::app.checkout.onepage.address.billing-address') }}"
                                        />
                                    </x-shop::form.control-group>

                                    <!-- Edit Icon -->
                                    <span
                                        class="icon-edit cursor-pointer text-2xl"
                                        @click="
                                            selectedAddressForEdit = address;
                                            activeAddressForm = 'billing';
                                            saveAddress = address.address_type == 'customer'
                                        "
                                    ></span>
                                </div>

                                <!-- Details -->
                                <label
                                    class="block cursor-pointer rounded-xl p-5 max-sm:rounded-lg"
                                    :for="`billing_address_id_${address.id}`"
                                >
                                    <span class="icon-checkout-address text-6xl text-navyBlue max-sm:text-5xl"></span>

                                    <div class="flex items-center justify-between">
                                        <p class="text-base font-medium">
                                            @{{ address.first_name + ' ' + address.last_name }}

                                            <template v-if="address.company_name">
                                                (@{{ address.company_name }})
                                            </template>
                                        </p>
                                    </div>

                                    <p class="mt-6 text-sm text-zinc-500 max-md:mt-2 max-sm:mt-0">
                                        <template v-if="address.address">
                                            @{{ address.address.join(', ') }},
                                        </template>

                                        @{{ address.city }},
                                        @{{ address.state }}, @{{ address.country }},
                                        @{{ address.postcode }}
                                    </p>
                                </label>
                            </div>

                            <!-- New Address Card -->
                            <div
                                class="flex max-w-[414px] cursor-pointer items-center justify-center rounded-xl border border-zinc-200 p-5 max-md:flex-wrap max-md:rounded-lg"
                                @click="activeAddressForm = 'billing'"
                                v-if="! cart.billing_address"
                            >
                                <div class="flex items-center gap-x-2.5" role="button" tabindex="0">
                                    <span class="icon-plus rounded-full border border-black p-2.5 text-3xl max-sm:p-2" role="presentation"></span>
                                    <p class="text-base">@lang('shop::app.checkout.onepage.address.add-new-address')</p>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message Block -->
                        <x-shop::form.control-group.error name="billing.id" />

                        <!-- Shipping Address Block if have stockable items -->
                        <template v-if="cart.have_stockable_items">
                            <!-- Use for Shipping Checkbox -->
                            <x-shop::form.control-group class="!mb-0 mt-5 flex items-center gap-2.5">
                                <x-shop::form.control-group.control
                                    type="checkbox"
                                    name="billing.use_for_shipping"
                                    id="use_for_shipping"
                                    for="use_for_shipping"
                                    value="1"
                                    @change="useBillingAddressForShipping = ! useBillingAddressForShipping"
                                    ::checked="!! useBillingAddressForShipping"
                                />

                                <label
                                    class="cursor-pointer select-none text-base text-zinc-500 max-md:text-sm max-sm:text-xs ltr:pl-0 rtl:pr-0"
                                    for="use_for_shipping"
                                >
                                    @lang('shop::app.checkout.onepage.address.same-as-billing')
                                </label>
                            </x-shop::form.control-group>

                            <!-- Customer Shipping Address -->
                            <div class="mt-8" v-if="! useBillingAddressForShipping">
                                <div class="mb-4 flex items-center justify-between">
                                    <h2 class="text-xl font-medium max-md:text-lg max-sm:text-base">
                                        @lang('shop::app.checkout.onepage.address.shipping-address')
                                    </h2>
                                </div>

                                <div class="mb-2 grid grid-cols-2 gap-5 max-1060:grid-cols-[1fr] max-lg:grid-cols-2 max-md:mt-4 max-md:grid-cols-1">
                                    <div
                                        class="relative max-w-[414px] cursor-pointer select-none rounded-xl border border-zinc-200 p-0 max-md:flex-wrap max-md:rounded-lg"
                                        v-for="address in customerSavedAddresses.shipping"
                                    >
                                        <div class="absolute top-5 flex gap-5 ltr:right-5 rtl:left-5">
                                            <x-shop::form.control-group class="!mb-0 flex items-center gap-2.5">
                                                <x-shop::form.control-group.control
                                                    type="radio"
                                                    name="shipping.id"
                                                    ::id="`shipping_address_id_${address.id}`"
                                                    ::for="`shipping_address_id_${address.id}`"
                                                    ::value="address.id"
                                                    v-model="selectedAddresses.shipping_address_id"
                                                    rules="required"
                                                    label="{{ trans('shop::app.checkout.onepage.address.shipping-address') }}"
                                                />
                                            </x-shop::form.control-group>

                                            <span
                                                class="icon-edit cursor-pointer text-2xl"
                                                @click="
                                                    selectedAddressForEdit = address;
                                                    activeAddressForm = 'shipping';
                                                    saveAddress = address.address_type == 'customer'
                                                "
                                            ></span>
                                        </div>

                                        <label
                                            class="block cursor-pointer rounded-xl p-5 max-md:rounded-lg"
                                            :for="`shipping_address_id_${address.id}`"
                                        >
                                            <span class="icon-checkout-address text-6xl text-navyBlue max-sm:text-5xl"></span>

                                            <div class="flex items-center justify-between">
                                                <p class="text-base font-medium">
                                                    @{{ address.first_name + ' ' + address.last_name }}

                                                    <template v-if="address.company_name">
                                                        (@{{ address.company_name }})
                                                    </template>
                                                </p>
                                            </div>

                                            <p class="mt-6 text-sm text-zinc-500 max-md:mt-2 max-sm:mt-0">
                                                <template v-if="address.address">
                                                    @{{ address.address.join(', ') }},
                                                </template>

                                                @{{ address.city }},
                                                @{{ address.state }}, @{{ address.country }},
                                                @{{ address.postcode }}
                                            </p>
                                        </label>
                                    </div>

                                    <!-- New Address Card -->
                                    <div
                                        class="flex max-w-[414px] cursor-pointer items-center justify-center rounded-xl border border-zinc-200 p-5 max-md:flex-wrap max-md:rounded-lg"
                                        @click="selectedAddressForEdit = null; activeAddressForm = 'shipping'"
                                        v-if="! cart.shipping_address"
                                    >
                                        <div class="flex items-center gap-x-2.5" role="button" tabindex="0">
                                            <span class="icon-plus rounded-full border border-black p-2.5 text-3xl max-sm:p-2" role="presentation"></span>
                                            <p class="text-base">@lang('shop::app.checkout.onepage.address.add-new-address')</p>
                                        </div>
                                    </div>
                                </div>

                                <x-shop::form.control-group.error name="shipping.id" />
                            </div>
                        </template>

                        <!-- Proceed Button -->
                        <div class="mt-4 flex justify-end max-md:my-4">
                            <x-shop::button
                                class="primary-button rounded-2xl px-11 py-3 max-md:rounded-lg max-sm:w-full max-sm:max-w-full max-sm:py-1.5"
                                :title="trans('shop::app.checkout.onepage.address.proceed')"
                                ::loading="isStoring"
                                ::disabled="isStoring"
                            />
                        </div>
                    </form>
                </x-shop::form>
            </template>

            <!-- Create/Edit Address Form -->
            <template v-else>
                <x-shop::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, updateOrCreateAddress)">
                        <!-- Header -->
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-medium max-md:text-base max-sm:font-normal">
                                <template v-if="activeAddressForm == 'billing'">
                                    @lang('shop::app.checkout.onepage.address.billing-address')
                                </template>
                                <template v-else>
                                    @lang('shop::app.checkout.onepage.address.shipping-address')
                                </template>
                            </h2>

                            <span
                                class="flex cursor-pointer justify-end"
                                v-show="customerSavedAddresses.billing.length && ['billing', 'shipping'].includes(activeAddressForm)"
                                @click="selectedAddressForEdit = null; activeAddressForm = null"
                            >
                                <span class="icon-arrow-left text-2xl max-md:hidden"></span>
                                @lang('shop::app.checkout.onepage.address.back')
                            </span>
                        </div>

                        <!-- Address Form Vue Component -->
                        <v-checkout-address-form
                            :control-name="activeAddressForm"
                            :address="selectedAddressForEdit || undefined"
                        ></v-checkout-address-form>

                        <!-- Save Address Checkbox -->
                        <x-shop::form.control-group class="!mb-0 flex items-center gap-2.5">
                            <x-shop::form.control-group.control
                                type="checkbox"
                                ::name="activeAddressForm + '.save_address'"
                                id="save_address"
                                for="save_address"
                                value="1"
                                v-model="saveAddress"
                                @change="saveAddress = ! saveAddress"
                            />

                            <label
                                class="cursor-pointer select-none text-base text-zinc-500 max-md:text-sm max-sm:text-xs ltr:pl-0 rtl:pr-0"
                                for="save_address"
                            >
                                @lang('shop::app.checkout.onepage.address.save-address')
                            </label>
                        </x-shop::form.control-group>

                        <!-- Save Button -->
                        <div class="mt-4 flex justify-end">
                            <x-shop::button
                                class="primary-button rounded-2xl px-11 py-3 max-md:rounded-lg max-sm:w-full max-sm:max-w-full max-sm:py-1.5"
                                :title="trans('shop::app.checkout.onepage.address.save')"
                                ::loading="isStoring"
                                ::disabled="isStoring"
                            />
                        </div>
                    </form>
                </x-shop::form>
            </template>
        </template>
    </script>

    <script type="module">
        app.component('v-checkout-address-customer', {
            template: '#v-checkout-address-customer-template',

            props: ['cart'],

            emits: ['processing', 'processed'],

            data() {
                return {
                    customerSavedAddresses: {
                        'billing': [],
                        'shipping': [],
                    },
                    useBillingAddressForShipping: true,
                    activeAddressForm: null,
                    selectedAddressForEdit: null,
                    saveAddress: false,
                    selectedAddresses: {
                        billing_address_id: null,
                        shipping_address_id: null,
                    },
                    isLoading: true,
                    isStoring: false,
                }
            },

            created() {
                if (this.cart.billing_address) {
                    this.useBillingAddressForShipping = this.cart.billing_address.use_for_shipping;
                }
            },

            mounted() {
                this.getCustomerSavedAddresses();
            },

            methods: {
                getCustomerSavedAddresses() {
                    this.$axios.get('{{ route('shop.api.customers.account.addresses.index') }}')
                        .then(response => {
                            this.initializeAddresses('billing',  structuredClone(response.data.data));
                            this.initializeAddresses('shipping', structuredClone(response.data.data));

                            if (!this.customerSavedAddresses.billing.length) {
                                this.activeAddressForm = 'billing';
                            }
                            this.isLoading = false;
                        })
                        .catch((error) => { console.error(error); });
                },

                initializeAddresses(type, addresses) {
                    this.customerSavedAddresses[type] = addresses;

                    let cartAddress = this.cart[type + '_address'];

                    if (!cartAddress) {
                        addresses.forEach(address => {
                            if (address.default_address) {
                                this.selectedAddresses[type + '_address_id'] = address.id;
                            }
                        });
                        return addresses;
                    }

                    if (cartAddress.parent_address_id) {
                        addresses.forEach(address => {
                            if (address.id == cartAddress.parent_address_id) {
                                this.selectedAddresses[type + '_address_id'] = address.id;
                            }
                        });
                    } else {
                        this.selectedAddresses[type + '_address_id'] = cartAddress.id;
                        addresses.unshift(cartAddress);
                    }

                    return addresses;
                },

                updateOrCreateAddress(params, { setErrors }) {
                    this.$emit('processing', 'address');

                    params = params[this.activeAddressForm];

                    let address = this.customerSavedAddresses[this.activeAddressForm].find(a => a.id == params.id);

                    if (!address) {
                        if (params.save_address) {
                            this.createCustomerAddress(params, { setErrors })
                                .then((response) => { this.addAddressToList(response.data.data); })
                                .catch(() => {});
                        } else {
                            this.addAddressToList(params);
                        }
                        return;
                    }

                    if (params.save_address) {
                        if (address.address_type == 'customer') {
                            this.updateCustomerAddress(params.id, params, { setErrors })
                                .then((response) => { this.updateAddressInList(response.data.data); })
                                .catch(() => {});
                        } else {
                            this.removeAddressFromList(params);
                            this.createCustomerAddress(params, { setErrors })
                                .then((response) => { this.addAddressToList(response.data.data); })
                                .catch(() => {});
                        }
                    } else {
                        this.updateAddressInList(params);
                    }
                },

                addAddressToList(address) {
                    this.cart[this.activeAddressForm + '_address'] = address;
                    this.customerSavedAddresses[this.activeAddressForm].unshift(address);
                    this.selectedAddresses[this.activeAddressForm + '_address_id'] = address.id;
                    this.activeAddressForm = null;
                },

                updateAddressInList(params) {
                    this.customerSavedAddresses[this.activeAddressForm].forEach((address, index) => {
                        if (address.id == params.id) {
                            params = { ...address, ...params };
                            this.cart[this.activeAddressForm + '_address'] = params;
                            this.customerSavedAddresses[this.activeAddressForm][index] = params;
                            this.selectedAddresses[this.activeAddressForm + '_address_id'] = params.id;
                            this.activeAddressForm = null;
                        }
                    });
                },

                removeAddressFromList(params) {
                    this.customerSavedAddresses[this.activeAddressForm] =
                        this.customerSavedAddresses[this.activeAddressForm].filter(a => a.id != params.id);
                },

                createCustomerAddress(params, { setErrors }) {
                    this.isStoring = true;
                    return this.$axios.post('{{ route('shop.api.customers.account.addresses.store') }}', params)
                        .then((response) => { this.isStoring = false; return response; })
                        .catch(error => {
                            this.isStoring = false;
                            if (error.response.status == 422) {
                                let errors = {};
                                Object.keys(error.response.data.errors).forEach(key => {
                                    errors[this.activeAddressForm + '.' + key] = error.response.data.errors[key];
                                });
                                setErrors(errors);
                            }
                            return Promise.reject(error);
                        });
                },

                updateCustomerAddress(id, params, { setErrors }) {
                    this.isStoring = true;
                    return this.$axios.put('{{ route('shop.api.customers.account.addresses.update') }}/' + id, params)
                        .then((response) => { this.isStoring = false; return response; })
                        .catch(error => {
                            this.isStoring = false;
                            if (error.response.status == 422) {
                                let errors = {};
                                Object.keys(error.response.data.errors).forEach(key => {
                                    errors[this.activeAddressForm + '.' + key] = error.response.data.errors[key];
                                });
                                setErrors(errors);
                            }
                            return Promise.reject(error);
                        });
                },

                addAddressToCart(params, { setErrors }) {
                    let payload = {
                        billing: {
                            ...this.getSelectedAddress('billing', params.billing.id),
                            use_for_shipping: this.useBillingAddressForShipping
                        },
                    };

                    if (params.shipping !== undefined) {
                        payload.shipping = this.getSelectedAddress('shipping', params.shipping.id);
                    }

                    this.isStoring = true;
                    this.moveToNextStep();

                    this.$axios.post('{{ route('shop.checkout.onepage.addresses.store') }}', payload)
                        .then((response) => {
                            this.isStoring = false;
                            if (response.data.data.redirect_url) {
                                window.location.href = response.data.data.redirect_url;
                            } else {
                                if (this.cart.have_stockable_items) {
                                    this.$emit('processed', response.data.data.shippingMethods);
                                } else {
                                    this.$emit('processed', response.data.data.payment_methods);
                                }
                            }
                        })
                        .catch(error => {
                            this.isStoring = false;
                            this.$emit('processing', 'address');
                            if (error.response.status == 422) {
                                const billingRegex = /^billing\./;
                                if (Object.keys(error.response.data.errors).some(key => billingRegex.test(key))) {
                                    setErrors({ 'billing.id': error.response.data.message });
                                } else {
                                    setErrors({ 'shipping.id': error.response.data.message });
                                }
                            }
                        });
                },

                getSelectedAddress(type, id) {
                    let address = Object.assign({}, this.customerSavedAddresses[type].find(a => a.id == id));
                    if (id == 0) address.id = null;
                    return { ...address, default_address: 0 };
                },

                moveToNextStep() {
                    if (this.cart.have_stockable_items) {
                        this.$emit('processing', 'shipping');
                    } else {
                        this.$emit('processing', 'payment');
                    }
                },
            }
        });
    </script>
@endPushOnce