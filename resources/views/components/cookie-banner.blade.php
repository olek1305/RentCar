<div id="cookie-banner" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-200 shadow-lg z-50 p-4 md:p-6">
    <div class="container mx-auto">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('messages.cookies_title') }}</h3>
                <p class="text-gray-600 text-sm">{{ __('messages.cookies_description') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 lg:ml-6">
                <button id="cookie-settings-btn" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium transition duration-200">
                    {{ __('messages.cookie_settings') }}
                </button>
                <button id="reject-all-btn" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium transition duration-200">
                    {{ __('messages.reject_all') }}
                </button>
                <button id="accept-all-btn" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition duration-200">
                    {{ __('messages.accept_all') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cookie Settings Modal -->
<div id="cookie-settings-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.cookie_settings') }}</h2>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" stroke="currentColor" viewBox="0 0 24 24">
                        <path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Essential Cookies -->
                <div class="border-b pb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('messages.cookies_essential') }}</h3>
                        <div class="flex items-center">
                            <input type="checkbox" id="essential-cookies" checked disabled class="h-4 w-4 text-red-600 rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-500">Always active</span>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm">{{ __('messages.cookies_essential_desc') }}</p>
                </div>

                <!-- Analytics Cookies -->
                <div class="border-b pb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('messages.cookies_analytics') }}</h3>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="analytics-cookies" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>
                    <p class="text-gray-600 text-sm">{{ __('messages.cookies_analytics_desc') }}</p>
                </div>

                <!-- Marketing Cookies -->
                <div class="pb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('messages.cookies_marketing') }}</h3>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="marketing-cookies" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>
                    <p class="text-gray-600 text-sm">{{ __('messages.cookies_marketing_desc') }}</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t">
                <a href="/privacy-policy" class="text-red-600 hover:text-red-700 text-sm underline">
                    {{ __('messages.privacy_policy') }}
                </a>
                <div class="flex-1"></div>
                <button id="save-preferences-btn" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition duration-200">
                    {{ __('messages.save_preferences') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    class CookieConsent {
        constructor() {
            this.init();
        }

        init() {
            // Check if the user has already selected their preferences
            if (!this.hasConsent()) {
                this.showBanner();
            }

            this.bindEvents();
            this.loadSavedPreferences();
        }

        bindEvents() {
            // Banner buttons
            document.getElementById('accept-all-btn')?.addEventListener('click', () => {
                this.acceptAll();
            });

            document.getElementById('reject-all-btn')?.addEventListener('click', () => {
                this.rejectAll();
            });

            document.getElementById('cookie-settings-btn')?.addEventListener('click', () => {
                this.showSettings();
            });

            // Modal buttons
            document.getElementById('close-modal-btn')?.addEventListener('click', () => {
                this.hideSettings();
            });

            document.getElementById('save-preferences-btn')?.addEventListener('click', () => {
                this.savePreferences();
            });

            // Click the outside modal to close
            document.getElementById('cookie-settings-modal')?.addEventListener('click', (e) => {
                if (e.target.id === 'cookie-settings-modal') {
                    this.hideSettings();
                }
            });
        }

        hasConsent() {
            return localStorage.getItem('cookie_consent') !== null;
        }

        showBanner() {
            document.getElementById('cookie-banner')?.classList.remove('hidden');
        }

        hideBanner() {
            document.getElementById('cookie-banner')?.classList.add('hidden');
        }

        showSettings() {
            document.getElementById('cookie-settings-modal')?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        hideSettings() {
            document.getElementById('cookie-settings-modal')?.classList.add('hidden');
            document.body.style.overflow = '';
        }

        acceptAll() {
            const consent = {
                essential: true,
                analytics: true,
                marketing: true,
                timestamp: new Date().toISOString()
            };

            this.saveConsent(consent);
            this.hideBanner();
            this.loadScripts(consent);
        }

        rejectAll() {
            const consent = {
                essential: true,
                analytics: false,
                marketing: false,
                timestamp: new Date().toISOString()
            };

            this.saveConsent(consent);
            this.hideBanner();
        }

        savePreferences() {
            const consent = {
                essential: true,
                analytics: document.getElementById('analytics-cookies').checked,
                marketing: document.getElementById('marketing-cookies').checked,
                timestamp: new Date().toISOString()
            };

            this.saveConsent(consent);
            this.hideSettings();
            this.hideBanner();
            this.loadScripts(consent);
        }

        saveConsent(consent) {
            localStorage.setItem('cookie_consent', JSON.stringify(consent));

            // Optional: Send to server for analytics
            // Add this route to web.php:
            /* Route::post('/api/cookie-consent', function (Request $request) {
                \Illuminate\Support\Facades\Log::info('Cookie consent saved', $request->all());
                return response()->json(['status' => 'success']);
            }); */

            // Send to server (optional)
            // fetch('/api/cookie-consent', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     },
            //     body: JSON.stringify(consent)
            // }).catch(console.error);
        }

        loadSavedPreferences() {
            const saved = localStorage.getItem('cookie_consent');
            if (saved) {
                const consent = JSON.parse(saved);
                document.getElementById('analytics-cookies').checked = consent.analytics;
                document.getElementById('marketing-cookies').checked = consent.marketing;
                this.loadScripts(consent);
            }
        }

        loadScripts(consent) {
            // Load Google Analytics if he agreed to analytics
            if (consent.analytics && !window.gtag) {
                this.loadGoogleAnalytics();
            }

            // Load Facebook Pixel if he agreed to marketing
            if (consent.marketing && !window.fbq) {
                this.loadFacebookPixel();
            }
        }

        loadGoogleAnalytics() {
            // Example of loading GA4
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID';
            document.head.appendChild(script);

            script.onload = () => {
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', 'GA_MEASUREMENT_ID');
                window.gtag = gtag;
            };
        }

        loadFacebookPixel() {
            // Example of loading Facebook Pixel
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', 'YOUR_PIXEL_ID');
            fbq('track', 'PageView');
        }

        // Method for checking compliance with other parts of the code
        static hasConsentFor(type) {
            const consent = localStorage.getItem('cookie_consent');
            if (!consent) return false;

            const parsed = JSON.parse(consent);
            return parsed[type] === true;
        }
    }

    // Initialize after loading the DOM
    document.addEventListener('DOMContentLoaded', () => {
        new CookieConsent();
    });

    // Export to global scope
    window.CookieConsent = CookieConsent;
</script>
