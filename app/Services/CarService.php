<?php

namespace App\Services;

use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CarService
{
    public function getVisibleCars(bool $includeHidden = false): Builder
    {
        $query = Car::query()->orderBy('created_at', 'desc');

        if (!auth()->check() || !$includeHidden) {
            $query->where('hidden', false);
        }

        return $query;
    }

    public function getCarTypes(): array
    {
        return Car::TYPES;
    }

    public function getFuelTypes(): array
    {
        return Car::fuelTypes;
    }

    public function getTransmissions(): array
    {
        return Car::transmissions;
    }

    public function prepareCarForDisplay(Car $car): Car
    {
        $car->main_image_url = Storage::url($car->main_image);

        if (is_string($car->rental_prices)) {
            $car->rental_prices = json_decode($car->rental_prices, true);
        }

        return $car;
    }

    public function prepareCarForEdit(Car $car): Car
    {
        if (is_string($car->rental_prices)) {
            $car->rental_prices = json_decode($car->rental_prices, true);
        }

        if (is_string($car->gallery_images)) {
            $car->gallery_images = json_decode($car->gallery_images, true) ?? [];
        }

        return $car;
    }

    public function checkCarVisibility(Car $car): void
    {
        if ($car->hidden && !auth()->check()) {
            abort(404);
        }
    }

    public function createCar(array $data, ?UploadedFile $mainImage, ?array $galleryImages): Car
    {
        if ($mainImage) {
            $data['main_image'] = $mainImage->store('cars/main', 'public');
        }

        if ($galleryImages) {
            $galleryPaths = [];
            foreach ($galleryImages as $image) {
                $galleryPaths[] = $image->store('cars/gallery', 'public');
            }
            $data['gallery_images'] = $galleryPaths;
        }

        $data['rental_prices'] = json_encode([
            '1-2' => $data['daily_price'],
            '3-6' => $data['daily_price'] * 0.9,
            '7+' => $data['daily_price'] * 0.8
        ]);

        unset($data['daily_price']);

        return Car::create($data);
    }

    public function updateCar(
        Car $car,
        array $data,
        ?UploadedFile $mainImage,
        ?array $galleryImages,
        ?string $newMainImage,
        ?bool $deleteOldMainImage,
        ?bool $deleteMainImage,
        ?array $deleteGalleryImages
    ): void {
        $galleryImagesArray = $this->prepareGalleryImages($car);

        // Handle image updates
        $this->handleImageUpdates(
            $car,
            $data,
            $mainImage,
            $galleryImages,
            $newMainImage,
            $deleteOldMainImage,
            $deleteMainImage,
            $deleteGalleryImages,
            $galleryImagesArray
        );

        // Update rental prices
        $data['rental_prices'] = [
            '1-2' => $data['daily_price'],
            '3-6' => $data['daily_price'] * 0.9,
            '7+' => $data['daily_price'] * 0.8
        ];
        unset($data['daily_price']);

        $car->update($data);
    }

    protected function prepareGalleryImages(Car $car): array
    {
        if (empty($car->gallery_images)) {
            return [];
        }

        return is_array($car->gallery_images)
            ? $car->gallery_images
            : (json_decode($car->gallery_images, true) ?? []);
    }

    protected function handleImageUpdates(
        Car $car,
        array &$data,
        ?UploadedFile $mainImage,
        ?array $galleryImages,
        ?string $newMainImage,
        ?bool $deleteOldMainImage,
        ?bool $deleteMainImage,
        ?array $deleteGalleryImages,
        array &$galleryImagesArray
    ): void {
        // Handle new main image from gallery
        if ($newMainImage) {
            if ($car->main_image && $deleteOldMainImage) {
                Storage::disk('public')->delete($car->main_image);
            } elseif ($car->main_image) {
                $galleryImagesArray[] = $car->main_image;
            }

            $data['main_image'] = $newMainImage;
            $galleryImagesArray = array_diff($galleryImagesArray, [$newMainImage]);
            $data['gallery_images'] = array_values(array_filter($galleryImagesArray));
        }

        // Handle main image deletion
        if ($deleteMainImage) {
            Storage::disk('public')->delete($car->main_image);
            $data['main_image'] = null;
        }

        // Handle gallery images deletion
        if ($deleteGalleryImages) {
            foreach ($deleteGalleryImages as $imageToDelete) {
                Storage::disk('public')->delete($imageToDelete);
            }
            $galleryImagesArray = array_diff($galleryImagesArray, $deleteGalleryImages);
            $data['gallery_images'] = array_values($galleryImagesArray);
        }

        // Handle new main image upload
        if ($mainImage) {
            if ($car->main_image) {
                $galleryImagesArray[] = $car->main_image;
                Storage::disk('public')->delete($car->main_image);
            }
            $data['main_image'] = $mainImage->store('cars/main', 'public');
        }

        // Handle new gallery images upload
        if ($galleryImages) {
            foreach ($galleryImages as $image) {
                $galleryImagesArray[] = $image->store('cars/gallery', 'public');
            }
            $data['gallery_images'] = $galleryImagesArray;
        }
    }

    public function deleteCar(Car $car): void
    {
        // Delete images
        if ($car->main_image) {
            Storage::disk('public')->delete($car->main_image);
        }

        if ($car->gallery_images) {
            $galleryImages = is_array($car->gallery_images)
                ? $car->gallery_images
                : json_decode($car->gallery_images, true);

            foreach ($galleryImages as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $car->delete();
    }

    public function toggleCarVisibility(Car $car): void
    {
        $car->update(['hidden' => !$car->hidden]);
    }
}
