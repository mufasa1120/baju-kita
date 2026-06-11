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

{{-- ============================================================ --}}
{{-- GOOGLE MAPS ADDRESS PICKER - Tambahkan API Key Anda di bawah --}}
{{-- Ganti YOUR_GOOGLE_MAPS_API_KEY dengan API key yang valid      --}}
{{-- Aktifkan: Places API & Maps JavaScript API di Google Console  --}}
{{-- ============================================================ --}}
@pushOnce('head')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXbmsnN9ezbAScphNgcMnpMEez5r0gHZI&libraries=places&callback=initGoogleMapsCallback" async defer></script>
    <style>
        /* Map Picker Modal */
        #google-map-picker-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.55);
            align-items: center;
            justify-content: center;
        }
        #google-map-picker-modal.active {
            display: flex;
        }
        #google-map-picker-box {
            background: #fff;
            border-radius: 16px;
            width: 96vw;
            max-width: 680px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.18);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        #google-map-picker-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        #google-map-picker-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }
        #google-map-picker-close {
            cursor: pointer;
            font-size: 1.5rem;
            color: #6b7280;
            border: none;
            background: none;
            line-height: 1;
        }
        #google-map-search-wrap {
            padding: 14px 20px 10px;
        }
        #google-map-search-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            box-sizing: border-box;
        }
        #google-map-search-input:focus {
            border-color: #6366f1;
        }
        #google-map-canvas {
            width: 100%;
            height: 340px;
            min-height: 340px;
            display: block;
            background: #e5e7eb;
            overflow: hidden;
        }
        /* Penting: reset CSS global Bagisto yang bisa ganggu render tiles Maps */
        #google-map-canvas img {
            max-width: none !important;
            display: inline !important;
        }
        #google-map-canvas * {
            box-sizing: content-box;
        }
        /* Picker box: overflow hidden tapi canvas tidak terclip */
        #google-map-picker-footer {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #e5e7eb;
            gap: 12px;
        }
        #google-map-selected-address {
            font-size: 0.85rem;
            color: #374151;
            flex: 1;
            word-break: break-word;
        }
        #google-map-confirm-btn {
            padding: 9px 22px;
            background: #1e293b;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        #google-map-confirm-btn:hover {
            background: #0f172a;
        }
        /* ---- Tombol Pilih dari Peta ---- */
        button.google-map-open-btn,
        button.google-map-open-btn:focus,
        button.google-map-open-btn:active {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 8px !important;
            margin-bottom: 12px !important;
            padding: 9px 16px !important;
            background: #f8fafc !important;
            border: 1.5px solid #94a3b8 !important;
            border-radius: 8px !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            cursor: pointer !important;
            line-height: 1.4 !important;
            text-decoration: none !important;
            box-shadow: none !important;
            width: auto !important;
            height: auto !important;
            min-height: unset !important;
            /* Reset any Bagisto icon font leaking in */
            font-family: inherit !important;
        }
        button.google-map-open-btn:hover {
            background: #e2e8f0 !important;
            border-color: #64748b !important;
        }
        button.google-map-open-btn::before,
        button.google-map-open-btn::after {
            display: none !important;
            content: none !important;
        }
        button.google-map-open-btn .gmap-btn-icon {
            width: 18px !important;
            height: 18px !important;
            flex-shrink: 0 !important;
            display: block !important;
            /* Override icon font yang mungkin diterapkan Bagisto */
            font-family: unset !important;
            font-size: unset !important;
        }
        button.google-map-open-btn .gmap-btn-icon svg {
            width: 18px !important;
            height: 18px !important;
            display: block !important;
        }
        button.google-map-open-btn .gmap-btn-label {
            font-family: inherit !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            line-height: 1 !important;
        }
    </style>
@endPushOnce

<!-- Google Map Address Picker Modal (shared untuk billing & shipping) -->
<div id="google-map-picker-modal" role="dialog" aria-modal="true" aria-label="Pilih Alamat dari Peta">
    <div id="google-map-picker-box">
        <div id="google-map-picker-header">
            <h3>📍 Pilih Alamat dari Peta</h3>
            <button id="google-map-picker-close" aria-label="Tutup">&times;</button>
        </div>
        <div id="google-map-search-wrap">
            <input
                id="google-map-search-input"
                type="text"
                placeholder="Cari alamat, nama jalan, atau tempat..."
                autocomplete="off"
            />
        </div>
        <div id="google-map-canvas"></div>
        <div id="google-map-picker-footer">
            <span id="google-map-selected-address">Klik pada peta atau cari untuk memilih lokasi</span>
            <button id="google-map-confirm-btn">Gunakan Alamat Ini</button>
        </div>
    </div>
</div>

@pushOnce('scripts')
    <script>
        // =============================================
        // Google Maps Address Picker – Global Handler
        // =============================================
        window._gmapPickerCallback = null;
        window._gmapInitialized = false;
        window._gmapMap = null;
        window._gmapMarker = null;
        window._gmapGeocoder = null;
        window._gmapAutocomplete = null;
        window._gmapSelectedPlace = null;

        window.initGoogleMapsCallback = function () {
            window._gmapInitialized = true;
        };

        window.openGoogleMapPicker = function (callback) {
            window._gmapPickerCallback = callback;
            window._gmapSelectedPlace = null;

            const modal = document.getElementById('google-map-picker-modal');
            modal.classList.add('active');
            document.getElementById('google-map-selected-address').textContent = 'Klik pada peta atau cari untuk memilih lokasi';
            document.getElementById('google-map-search-input').value = '';

            // PENTING: Tunggu modal benar-benar rendered (display:flex) baru init/resize map
            // Tanpa ini, canvas height = 0 dan Google Maps tidak bisa render tiles
            setTimeout(function () {
                if (!window._gmapMap && window._gmapInitialized) {
                    initMap();
                } else if (window._gmapMap) {
                    // Resize wajib dipanggil setelah modal visible
                    google.maps.event.trigger(window._gmapMap, 'resize');
                    if (window._gmapMarker && window._gmapMarker.getPosition()) {
                        window._gmapMap.panTo(window._gmapMarker.getPosition());
                    }
                } else {
                    // Google Maps JS belum selesai load, tunggu
                    const waitForGmaps = setInterval(function () {
                        if (window._gmapInitialized) {
                            clearInterval(waitForGmaps);
                            setTimeout(initMap, 50);
                        }
                    }, 100);
                }
            }, 80); // 80ms cukup untuk browser flush layout modal
        };

        function initMap() {
            const defaultCenter = { lat: -6.2088, lng: 106.8456 }; // Jakarta
            const mapDiv = document.getElementById('google-map-canvas');

            window._gmapMap = new google.maps.Map(mapDiv, {
                center: defaultCenter,
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            window._gmapGeocoder = new google.maps.Geocoder();

            window._gmapMarker = new google.maps.Marker({
                map: window._gmapMap,
                draggable: true,
                visible: false,
            });

            // Coba gunakan lokasi user
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    const userLoc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    window._gmapMap.setCenter(userLoc);
                    window._gmapMap.setZoom(15);
                });
            }

            // Klik pada peta → set marker & reverse geocode
            window._gmapMap.addListener('click', function (e) {
                placeMarkerAndGeocode(e.latLng);
            });

            // Drag marker selesai → reverse geocode
            window._gmapMarker.addListener('dragend', function () {
                placeMarkerAndGeocode(window._gmapMarker.getPosition());
            });

            // Autocomplete search
            const input = document.getElementById('google-map-search-input');
            window._gmapAutocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: { country: 'id' }, // Batasi ke Indonesia, hapus jika perlu
                fields: ['geometry', 'formatted_address', 'address_components'],
            });

            window._gmapAutocomplete.addListener('place_changed', function () {
                const place = window._gmapAutocomplete.getPlace();
                if (!place.geometry) return;

                window._gmapMap.setCenter(place.geometry.location);
                window._gmapMap.setZoom(17);
                window._gmapMarker.setPosition(place.geometry.location);
                window._gmapMarker.setVisible(true);
                parseAndSetPlace(place);
            });
        }

        function placeMarkerAndGeocode(latLng) {
            window._gmapMarker.setPosition(latLng);
            window._gmapMarker.setVisible(true);

            window._gmapGeocoder.geocode({ location: latLng }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    parseAndSetPlace(results[0]);
                }
            });
        }

        function parseAndSetPlace(place) {
            const components = place.address_components || [];
            const get = function (types) {
                const c = components.find(function (c) { return types.some(function (t) { return c.types.includes(t); }); });
                return c ? c.long_name : '';
            };
            const getShort = function (types) {
                const c = components.find(function (c) { return types.some(function (t) { return c.types.includes(t); }); });
                return c ? c.short_name : '';
            };

            // Ambil komponen alamat dari Google
            const streetNumber = get(['street_number']);
            const route = get(['route']);
            const kelurahan = get(['administrative_area_level_4', 'sublocality_level_2']);
            const kecamatan = get(['administrative_area_level_3', 'sublocality_level_1', 'sublocality']);
            const city = get(['administrative_area_level_2', 'locality']);
            const state = get(['administrative_area_level_1']);
            const postcode = get(['postal_code']);
            const country = getShort(['country']);

            const streetLine = [streetNumber, route].filter(Boolean).join(' ') || kecamatan || place.formatted_address || '';

            window._gmapSelectedPlace = {
                address: streetLine,
                city: city,
                state: state,
                postcode: postcode,
                country: country,
                formatted: place.formatted_address || streetLine,
            };

            document.getElementById('google-map-selected-address').textContent = '📍 ' + (place.formatted_address || streetLine);
        }

        // Tutup modal — gunakan pattern yang aman di Bagisto
        // (DOMContentLoaded mungkin sudah lewat saat script ini dieksekusi)
        function gmapSetupModalListeners() {
            const closeBtn = document.getElementById('google-map-picker-close');
            const modal = document.getElementById('google-map-picker-modal');
            const confirmBtn = document.getElementById('google-map-confirm-btn');

            if (!closeBtn || !modal || !confirmBtn) {
                // Elemen belum ada, coba lagi sebentar
                setTimeout(gmapSetupModalListeners, 100);
                return;
            }

            closeBtn.addEventListener('click', function () {
                modal.classList.remove('active');
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });

            confirmBtn.addEventListener('click', function () {
                if (!window._gmapSelectedPlace) {
                    alert('Silakan pilih lokasi di peta terlebih dahulu.');
                    return;
                }
                if (typeof window._gmapPickerCallback === 'function') {
                    window._gmapPickerCallback(window._gmapSelectedPlace);
                }
                modal.classList.remove('active');
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    modal.classList.remove('active');
                }
            });
        }

        // Jalankan segera (tidak perlu tunggu DOMContentLoaded)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', gmapSetupModalListeners);
        } else {
            gmapSetupModalListeners();
        }
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
                                <div
                                    class="flex items-center gap-x-2.5"
                                    role="button"
                                    tabindex="0"
                                >
                                    <span
                                        class="icon-plus rounded-full border border-black p-2.5 text-3xl max-sm:p-2"
                                        role="presentation"
                                    ></span>

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
                            <div
                                class="mt-8"
                                v-if="! useBillingAddressForShipping"
                            >
                                <!-- Shipping Address Header -->
                                <div class="mb-4 flex items-center justify-between">
                                    <h2 class="text-xl font-medium max-md:text-lg max-sm:text-base">
                                        @lang('shop::app.checkout.onepage.address.shipping-address')
                                    </h2>
                                </div>

                                <!-- Saved Customer Addresses Cards -->
                                <div class="mb-2 grid grid-cols-2 gap-5 max-1060:grid-cols-[1fr] max-lg:grid-cols-2 max-md:mt-4 max-md:grid-cols-1">
                                    <div
                                        class="relative max-w-[414px] cursor-pointer select-none rounded-xl border border-zinc-200 p-0 max-md:flex-wrap max-md:rounded-lg"
                                        v-for="address in customerSavedAddresses.shipping"
                                    >
                                        <!-- Actions -->
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

                                            <!-- Edit Icon -->
                                            <span
                                                class="icon-edit cursor-pointer text-2xl"
                                                @click="
                                                    selectedAddressForEdit = address;
                                                    activeAddressForm = 'shipping';
                                                    saveAddress = address.address_type == 'customer'
                                                "
                                            ></span>
                                        </div>

                                        <!-- Details -->
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
                                        <div
                                            class="flex items-center gap-x-2.5"
                                            role="button"
                                            tabindex="0"
                                        >
                                            <span
                                                class="icon-plus rounded-full border border-black p-2.5 text-3xl max-sm:p-2"
                                                role="presentation"
                                            ></span>

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
                        <!-- Billing Address Header -->
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
                        
                        <!-- ===== TOMBOL PILIH DARI GOOGLE MAPS ===== -->
                        <div class="mb-4">
                            <button
                                type="button"
                                class="google-map-open-btn"
                                @click="openMapPicker"
                            >
                                <span class="gmap-btn-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                    </svg>
                                </span>
                                <span class="gmap-btn-label">🗺 Pilih Lokasi dari Peta</span>
                            </button>
                            <p v-if="mapFilledAddress" style="font-size:0.82rem;color:#16a34a;margin-top:4px;">
                                ✓ Alamat dari peta: @{{ mapFilledAddress }}
                            </p>
                        </div>
                        <!-- =========================================== -->

                        <!-- Address Form Vue Component -->
                        <v-checkout-address-form
                            :control-name="activeAddressForm"
                            :address="selectedAddressForEdit || undefined"
                            :map-address="mapAddressData"
                            @address-form-ready="onAddressFormReady"
                        ></v-checkout-address-form>

                        <!-- Save Address to Address Book Checkbox -->
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

                    // ===== Google Maps Address Picker =====
                    mapFilledAddress: null,
                    mapAddressData: null,
                    // ======================================
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

            watch: {
                // ===== Watch: Isi form otomatis saat alamat dipilih dari peta =====
                mapAddressData(place) {
                    if (!place) return;

                    const prefix = this.activeAddressForm; // 'billing' or 'shipping'

                    // Fungsi helper: set nilai input dan trigger Vue reactivity
                    const fillField = (selector, value) => {
                        const el = document.querySelector(selector);
                        if (el && value) {
                            const nativeSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                            nativeSetter.call(el, value);
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    };

                    // Isi field berdasarkan name attribute yang digunakan Bagisto
                    this.$nextTick(() => {
                        fillField(`input[name="${prefix}[address][]"]`, place.address);
                        fillField(`input[name="${prefix}[city]"]`, place.city);
                        fillField(`input[name="${prefix}[postcode]"]`, place.postcode);

                        // State & Country: coba set select element
                        const stateEl = document.querySelector(`select[name="${prefix}[state]"], input[name="${prefix}[state]"]`);
                        if (stateEl && place.state) {
                            const nativeSetter = Object.getOwnPropertyDescriptor(
                                stateEl.tagName === 'SELECT'
                                    ? window.HTMLSelectElement.prototype
                                    : window.HTMLInputElement.prototype,
                                'value'
                            ).set;
                            nativeSetter.call(stateEl, place.state);
                            stateEl.dispatchEvent(new Event('input', { bubbles: true }));
                            stateEl.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        const countryEl = document.querySelector(`select[name="${prefix}[country]"], input[name="${prefix}[country]"]`);
                        if (countryEl && place.country) {
                            const nativeSetter = Object.getOwnPropertyDescriptor(
                                countryEl.tagName === 'SELECT'
                                    ? window.HTMLSelectElement.prototype
                                    : window.HTMLInputElement.prototype,
                                'value'
                            ).set;
                            nativeSetter.call(countryEl, place.country);
                            countryEl.dispatchEvent(new Event('input', { bubbles: true }));
                            countryEl.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                },
                // ==================================================================
            },

            methods: {
                // ===== Google Maps Address Picker Methods =====
                openMapPicker() {
                    window.openGoogleMapPicker((place) => {
                        this.mapFilledAddress = place.formatted;
                        this.mapAddressData = place;
                    });
                },

                onAddressFormReady(formInstance) {
                    this._addressFormInstance = formInstance;
                },
                // ==============================================

                getCustomerSavedAddresses() {
                    this.$axios.get('{{ route('shop.api.customers.account.addresses.index') }}')
                        .then(response => {
                            this.initializeAddresses('billing', structuredClone(response.data.data));

                            this.initializeAddresses('shipping', structuredClone(response.data.data));

                            if (! this.customerSavedAddresses.billing.length) {
                                this.activeAddressForm = 'billing';
                            }

                            this.isLoading = false;
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                },

                initializeAddresses(type, addresses) {
                    this.customerSavedAddresses[type] = addresses;

                    let cartAddress = this.cart[type + '_address'];

                    if (! cartAddress) {
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

                    let address = this.customerSavedAddresses[this.activeAddressForm].find(address => {
                        return address.id == params.id;
                    });

                    if (! address) {
                        if (params.save_address) {
                            this.createCustomerAddress(params, { setErrors })
                                .then((response) => {
                                    this.addAddressToList(response.data.data);
                                })
                                .catch((error) => {});
                        } else {
                            this.addAddressToList(params);
                        }

                        return;
                    }

                    if (params.save_address) {
                        if (address.address_type == 'customer') {
                            this.updateCustomerAddress(params.id, params, { setErrors })
                                .then((response) => {
                                    this.updateAddressInList(response.data.data);
                                })
                                .catch((error) => {});
                        } else {
                            this.removeAddressFromList(params);

                            this.createCustomerAddress(params, { setErrors })
                                .then((response) => {
                                    this.addAddressToList(response.data.data);
                                })
                                .catch((error) => {});
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
                            params = {
                                ...address,
                                ...params,
                            };

                            this.cart[this.activeAddressForm + '_address'] = params;

                            this.customerSavedAddresses[this.activeAddressForm][index] = params;

                            this.selectedAddresses[this.activeAddressForm + '_address_id'] = params.id;

                            this.activeAddressForm = null;
                        }
                    });
                },

                removeAddressFromList(params) {
                    this.customerSavedAddresses[this.activeAddressForm] = this.customerSavedAddresses[this.activeAddressForm].filter(address => address.id != params.id);
                },

                createCustomerAddress(params, { setErrors }) {
                    this.isStoring = true;

                    return this.$axios.post('{{ route('shop.api.customers.account.addresses.store') }}', params)
                        .then((response) => {
                            this.isStoring = false;

                            return response;
                        })
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
                        .then((response) => {
                            this.isStoring = false;

                            return response;
                        })
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
                                    setErrors({
                                        'billing.id': error.response.data.message
                                    });
                                } else {
                                    setErrors({
                                        'shipping.id': error.response.data.message
                                    });
                                }
                            }
                        });
                },

                getSelectedAddress(type, id) {
                    let address = Object.assign({}, this.customerSavedAddresses[type].find(address => address.id == id));

                    if (id == 0) {
                        address.id = null;
                    }

                    return {
                        ...address,
                        default_address: 0,
                    };
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