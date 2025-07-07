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
        $types = Car::TYPES;
        $fuelTypes = Car::fuelTypes;
        $transmissions = Car::transmissions;

        return view('cars.create', compact('types', 'fuelTypes', 'transmissions'));
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

        return redirect()->route('cars.show', $car->id)->with('success', __('Car created successfully!'));
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
        $types = Car::TYPES;
        $fuelTypes = Car::fuelTypes;
        $transmissions = Car::transmissions;

        return view('cars.edit',  compact('car', 'types', 'fuelTypes', 'transmissions'));
    }

    /**
     * @param UpdateCarRequest $request
     * @param Car $car
     * @return RedirectResponse
     */
    public function update(UpdateCarRequest $request, Car $car): RedirectResponse
    {
        $validated = $request->validated();

        // Handle main image deletion
        if ($request->has('delete_main_image')) {
            Storage::disk('public')->delete($car->main_image);
            $validated['main_image'] = null;
        }

        // Handle new main image from gallery
        if ($request->new_main_image) {
            // Delete old main image if exists
            if ($car->main_image) {
                Storage::disk('public')->delete($car->main_image);
            }

            // Set new main image
            $validated['main_image'] = $request->new_main_image;

            // Remove from gallery
            $gallery = $car->gallery_images ?? [];
            $gallery = array_diff($gallery, [$request->new_main_image]);
            $validated['gallery_images'] = array_values($gallery); // Reindex array
        }

        // Handle gallery images deletion
        if ($request->delete_gallery_images) {
            foreach ($request->delete_gallery_images as $imageToDelete) {
                Storage::disk('public')->delete($imageToDelete);
            }

            $gallery = $car->gallery_images ?? [];
            $gallery = array_diff($gallery, $request->delete_gallery_images);
            $validated['gallery_images'] = array_values($gallery); // Reindex array
        }

        // Handle new main image upload
        if ($request->hasFile('main_image')) {
            if ($car->main_image) {
                Storage::disk('public')->delete($car->main_image);
            }
            $validated['main_image'] = $request->file('main_image')
                ->store('cars/main', 'public');
        }

        // Handle new gallery images upload
        if ($request->hasFile('gallery_images')) {
            $gallery = $validated['gallery_images'] ?? [];
            foreach ($request->file('gallery_images') as $image) {
                $gallery[] = $image->store('cars/gallery', 'public');
            }
            $validated['gallery_images'] = $gallery;
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
            ->with('success', __('Car updated successfully!'));
    }

    /**
     * @param Car $car
     * @return RedirectResponse
     */
    public function destroy(Car $car): RedirectResponse
    {
        $car->delete();

        return redirect()->route('cars')->with('success', 'Car deleted successfully.');
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
