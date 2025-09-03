<x-layout>
    <x-slot:title>{{ $car->model }} - {{ __('messages.details') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">{{ $car->model }} ({{ $car->year }})</h1>

        @include('cars.partials.image-gallery')
        @include('cars.partials.specifications')
        @include('cars.partials.description')
        @include('cars.partials.pricing')
        @include('cars.partials.booking-form')
    </section>

    @include('cars.partials.image-modal')

    <script>
        // Global variables
        let currentImageIndex = 0;
        let images = [];
        let rotationInterval;
        let isRotating = false;
        let carPrices = {};

        // Initialize on a page load
        document.addEventListener('DOMContentLoaded', function () {
            // Get data from a window object
            images = window.carData?.images || [];
            carPrices = window.carData?.prices || {};
            isRotating = images.length > 1;

            initializeForm();
            initializeImageGallery();
        });

        function initializeForm() {
            // Set default times and dates
            const now = new Date();
            let defaultRentalHour = now.getHours();
            if (defaultRentalHour < 6) defaultRentalHour = 6;
            if (defaultRentalHour > 20) defaultRentalHour = 9;

            const defaultMinutes = (Math.round(now.getMinutes() / 5) * 5).toString().padStart(2, '0');

            document.getElementById('rental_time_hour').value = defaultRentalHour.toString().padStart(2, '0');
            document.getElementById('rental_time_minute').value = defaultMinutes;

            let defaultReturnHour = defaultRentalHour + 1;
            if (defaultReturnHour > 20) defaultReturnHour = 20;

            document.getElementById('return_time_hour').value = defaultReturnHour.toString().padStart(2, '0');
            document.getElementById('return_time_minute').value = defaultMinutes;

            // Set default dates
            document.getElementById('rental_date').value = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('return_date').value = tomorrow.toISOString().split('T')[0];

            // Add event listeners
            document.getElementById('rental_date').addEventListener('change', calculateCosts);
            document.getElementById('return_date').addEventListener('change', calculateCosts);
            document.getElementById('additional_insurance').addEventListener('change', calculateCosts);

            document.querySelectorAll('input[name="delivery_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleDeliveryAddress();
                    calculateCosts();
                });
            });

            toggleDeliveryAddress();
            calculateCosts();
        }

        // Time adjustment functions
        function adjustTime(type, unit, change) {
            const input = document.querySelector(`input[name="${type}_time_${unit}"]`);
            const hourInput = document.querySelector(`input[name="${type}_time_hour"]`);
            const minuteInput = document.querySelector(`input[name="${type}_time_minute"]`);

            let value = parseInt(input.value) + change;
            let currentHour = parseInt(hourInput.value);

            if (unit === 'hour') {
                if (change > 0 && currentHour === 20) {
                    value = 6;
                } else if (change < 0 && currentHour === 6) {
                    value = 20;
                } else {
                    value = Math.max(6, Math.min(20, value));
                }

                if (value === 20) {
                    minuteInput.value = '00';
                }
            } else { // minute
                if (currentHour === 20 && value > 0) {
                    hourInput.value = '06';
                    value = Math.min(value, 55);
                    value = Math.floor(value / 5) * 5;
                } else if (currentHour === 6 && value < 0) {
                    hourInput.value = '20';
                    value = 0;
                } else if (value >= 60) {
                    value = 0;
                    let hourValue = currentHour + 1;
                    if (hourValue > 20) hourValue = 6;
                    if (hourValue === 20) value = 0;
                    hourInput.value = hourValue.toString().padStart(2, '0');
                } else if (value < 0) {
                    value = 55;
                    let hourValue = currentHour - 1;
                    if (hourValue < 6) {
                        hourValue = 20;
                        value = 0;
                    }
                    hourInput.value = hourValue.toString().padStart(2, '0');
                }

                value = Math.floor(value / 5) * 5;

                if (parseInt(hourInput.value) === 20) {
                    value = 0;
                }
            }

            input.value = value.toString().padStart(2, '0');
            calculateCosts();
        }

        // Cost calculation
        function calculateCosts() {
            const rentalDate = new Date(document.getElementById('rental_date').value);
            const returnDate = new Date(document.getElementById('return_date').value);

            if (isNaN(rentalDate) || isNaN(returnDate) || returnDate <= rentalDate) {
                updateCostDisplay(0, 0, 0, 0);
                return;
            }

            const diffTime = returnDate - rentalDate;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            let dailyRate;
            if (diffDays <= 2) {
                dailyRate = carPrices['1-2'];
            } else if (diffDays <= 6) {
                dailyRate = carPrices['3-6'];
            } else {
                dailyRate = carPrices['7+'];
            }
            const rentalCost = dailyRate * diffDays;

            const insuranceSelected = document.getElementById('additional_insurance').checked;
            const insuranceCost = insuranceSelected ? 15 * diffDays : 0;

            const deliveryOption = document.querySelector('input[name="delivery_option"]:checked')?.value || 'pickup';
            let deliveryCost = 0;
            if (deliveryOption === 'airport') {
                deliveryCost = 50;
            } else if (deliveryOption === 'delivery') {
                deliveryCost = 75;
            }

            updateCostDisplay(diffDays, rentalCost, insuranceCost, deliveryCost);
        }

        function updateCostDisplay(days, rental, insurance, delivery) {
            document.getElementById('rental-days').textContent = days;
            document.getElementById('rental-cost').textContent = rental.toFixed(2) + ' €';
            document.getElementById('insurance-cost').textContent = insurance.toFixed(2) + ' €';
            document.getElementById('delivery-cost').textContent = delivery.toFixed(2) + ' €';

            const totalRentalAmount = rental + insurance + delivery;
            document.getElementById('total-rental-amount').textContent = totalRentalAmount.toFixed(2) + ' €';

            const reservationFee = 5;
            document.getElementById('total-amount').textContent = reservationFee.toFixed(2) + ' €';
            document.getElementById('reservation-button-text').textContent = reservationFee.toFixed(2) + ' €';

            document.getElementById('insurance-cost-row').style.display = insurance > 0 ? 'flex' : 'none';
            document.getElementById('delivery-cost-row').style.display = delivery > 0 ? 'flex' : 'none';

            document.getElementById('total_amount').value = reservationFee.toFixed(2);
        }

        // Delivery address toggle
        function toggleDeliveryAddress() {
            const deliveryService = document.getElementById('delivery_service');
            const deliveryAddressField = document.getElementById('delivery-address-field');
            const deliveryAddressInput = document.getElementById('delivery_address');

            if (deliveryService && deliveryService.checked) {
                deliveryAddressField.classList.remove('hidden');
                deliveryAddressInput.setAttribute('required', 'required');
            } else {
                deliveryAddressField.classList.add('hidden');
                deliveryAddressInput.removeAttribute('required');
                deliveryAddressInput.value = '';
            }
        }

        // Image gallery functions
        function initializeImageGallery() {
            if (isRotating && images.length > 1) {
                startRotation();
            }
        }

        function startRotation() {
            isRotating = true;
            rotationInterval = setInterval(() => {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                changeMainImage(images[currentImageIndex], currentImageIndex);
            }, 3500);
            updateRotationButtons();
        }

        function stopRotation() {
            isRotating = false;
            clearInterval(rotationInterval);
            updateRotationButtons();
        }

        function toggleRotation() {
            if (isRotating) {
                stopRotation();
            } else {
                startRotation();
            }
        }

        function updateRotationButtons() {
            document.getElementById('play-rotation')?.classList.toggle('hidden', isRotating);
            document.getElementById('pause-rotation')?.classList.toggle('hidden', !isRotating);
        }

        function manualChangeImage(src, index) {
            stopRotation();
            changeMainImage(src, index);
        }

        function changeMainImage(src, index) {
            const storageUrl = document.querySelector('meta[name="storage-url"]')?.content || '/storage/';
            document.getElementById('main-car-image').src = storageUrl + src;
            currentImageIndex = index;

            document.querySelectorAll('.thumbnail-container').forEach((container, i) => {
                container.classList.toggle('border-blue-500', i === index);
                container.classList.toggle('border-transparent', i !== index);
            });
        }

        function openModal() {
            if (images.length === 0) return;
            stopRotation();
            const modal = document.getElementById('image-modal');
            const storageUrl = document.querySelector('meta[name="storage-url"]')?.content || '/storage/';
            document.getElementById('modal-image').src = storageUrl + images[currentImageIndex];
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('image-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            if (images.length > 1) {
                startRotation();
            }
        }

        // Event listeners for modal
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('image-modal')?.addEventListener('click', function (e) {
                if (e.target.id === 'image-modal') {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !document.getElementById('image-modal')?.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Form validation
            document.getElementById('booking-form')?.addEventListener('submit', function (e) {
                const rentalHour = parseInt(document.getElementById('rental_time_hour').value);
                const returnHour = parseInt(document.getElementById('return_time_hour').value);

                if (rentalHour < 6 || rentalHour > 20) {
                    e.preventDefault();
                    alert(window.messages.pickupHoursAlert);
                    return;
                }

                if (returnHour < 6 || returnHour > 20) {
                    e.preventDefault();
                    alert(window.messages.returnHoursAlert);
                    return;
                }

                const deliveryService = document.getElementById('delivery_service');
                const deliveryAddress = document.getElementById('delivery_address');

                if (deliveryService && deliveryService.checked && (!deliveryAddress.value || deliveryAddress.value.trim() === '')) {
                    e.preventDefault();
                    alert(window.messages.delivery_address_required);
                    deliveryAddress.focus();
                }
            });
        });
    </script>
</x-layout>
