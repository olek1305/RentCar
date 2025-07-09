<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use App\Models\Car;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    /**
     * @return Factory|Application|View
     */
    public function index(): Factory|Application|View
    {
        $cars = Car::visible()->orderBy('created_at', 'desc')->paginate(12);

        $cars->getCollection()->transform(function ($car) {
            $car->main_image_url = Storage::url($car->main_image);

            if (is_string($car->rental_prices)) {
                $car->rental_prices = json_decode($car->rental_prices, true);
            }

            return $car;
        });
        return view('cars.index', compact('cars'));
    }

    public function create(): Factory|Application|View
    {
        return view('cars.create', [
            'types' => Car::TYPES,
            'fuelTypes' => Car::fuelTypes,
            'transmissions' => Car::transmissions
        ]);
    }

    /**
     * @param StoreCarRequest $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(StoreCarRequest $request): Application|Redirector|RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')
                ->store('cars/main', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $image) {
                $galleryPaths[] = $image->store('cars/gallery', 'public');
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        $validated['rental_prices'] = json_encode([
            '1-2' => $validated['daily_price'],
            '3-6' => $validated['daily_price'] * 0.9,
            '7+' => $validated['daily_price'] * 0.8
        ]);

        unset($validated['daily_price']);

        $car = Car::create($validated);

        return redirect()->route('cars.show', $car->id)->with('success', __('messages.car_created'));
    }

    /**
     * @param Car $car
     * @return Factory|Application|View
     */
    public function show(Car $car): Factory|Application|View
    {
        // for auth
        if ($car->hidden && !auth()->check()) {
            abort(404);
        }

        return view('cars.show', compact('car'));
    }

    /**
     * @param Car $car
     * @return Factory|Application|View
     */
    public function edit(Car $car): Factory|Application|View
    {
        if (is_string($car->rental_prices)) {
            $car->rental_prices = json_decode($car->rental_prices, true);
        }

        if (is_string($car->gallery_images)) {
            $car->gallery_images = json_decode($car->gallery_images, true) ?? [];
        }

        return view('cars.edit', [
            'car' => $car,
            'types' => Car::TYPES,
            'fuelTypes' => Car::fuelTypes,
            'transmissions' => Car::transmissions
        ]);
    }

    /**
     * @param UpdateCarRequest $request
     * @param Car $car
     * @return RedirectResponse
     */
    public function update(UpdateCarRequest $request, Car $car): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure gallery_images is always an array
        $galleryImages = [];
        if (!empty($car->gallery_images)) {
            if (is_array($car->gallery_images)) {
                $galleryImages = $car->gallery_images;
            } else {
                $decoded = json_decode($car->gallery_images, true);
                $galleryImages = is_array($decoded) ? $decoded : [];
            }
        }

        // Handle new main image from gallery
        if ($request->new_main_image) {
            // Delete old main image if exists and checkbox is checked
            if ($car->main_image && $request->has('delete_old_main_image')) {
                Storage::disk('public')->delete($car->main_image);
            } elseif ($car->main_image) {
                // Add current main image to gallery if not deleting
                $galleryImages[] = $car->main_image;
            }

            // Set new main image
            $validated['main_image'] = $request->new_main_image;

            // Remove new main image from gallery
            $galleryImages = array_diff($galleryImages, [$request->new_main_image]);
            $validated['gallery_images'] = array_values(array_filter($galleryImages));
        }

        // Handle main image deletion
        if ($request->has('delete_main_image')) {
            Storage::disk('public')->delete($car->main_image);
            $validated['main_image'] = null;
        }

        // Handle gallery images deletion
        if ($request->delete_gallery_images) {
            foreach ($request->delete_gallery_images as $imageToDelete) {
                Storage::disk('public')->delete($imageToDelete);
            }

            $galleryImages = array_diff($galleryImages, $request->delete_gallery_images);
            $validated['gallery_images'] = array_values($galleryImages); // Reindex array
        }

        // Handle new main image upload
        if ($request->hasFile('main_image')) {
            // Add current main image to gallery if exists
            if ($car->main_image) {
                $galleryImages[] = $car->main_image;
                Storage::disk('public')->delete($car->main_image);
            }
            $validated['main_image'] = $request->file('main_image')
                ->store('cars/main', 'public');
        }

        // Handle new gallery images upload
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $galleryImages[] = $image->store('cars/gallery', 'public');
            }
            $validated['gallery_images'] = $galleryImages;
        }

        // Update rental prices
        $validated['rental_prices'] = [
            '1-2' => $validated['daily_price'],
            '3-6' => $validated['daily_price'] * 0.9,
            '7+' => $validated['daily_price'] * 0.8
        ];
        unset($validated['daily_price']);

        $car->update($validated);

        return redirect()->route('cars.show', $car->id)
            ->with('success', __('messages.car_updated'));
    }

    /**
     * @param Car $car
     * @return RedirectResponse
     */
    public function destroy(Car $car): RedirectResponse
    {
        $car->delete();

        return redirect()->route('cars.index')->with('success', __('messages.car_deleted'));
    }

    /**
     * Switch hide the car
     * @param Car $car
     * @return RedirectResponse
     */
    public function toggleVisibility(Car $car): RedirectResponse
    {
        $car->update(['hidden' => !$car->hidden]);

        return back()->with([
            'success' => 'Car visibility updated',
            'scroll_position' => request()->header('Referer') . '#car-' . $car->id
        ]);
    }
}
