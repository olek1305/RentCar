import './bootstrap';
import '@dmuy/timepicker/dist/mdtimepicker.css'
import mdtimepicker from '@dmuy/timepicker'

document.addEventListener('DOMContentLoaded', () => {
    const rentalTime = document.querySelector('input[name="rental_time"]');
    const returnTime = document.querySelector('input[name="return_time"]');

    if (rentalTime) mdtimepicker(rentalTime, { theme: 'blue' });
    if (returnTime) mdtimepicker(returnTime, { theme: 'blue' });
});
