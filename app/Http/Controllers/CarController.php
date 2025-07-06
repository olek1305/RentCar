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
    public function index(Request $request): Factory|Application|View
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $cars = Car::visible()->orderBy('created_at', 'desc')->paginate(12);

        $cars->getCollection()->transform(function ($car) {
            $car->main_image_url = Storage::url($car->main_image);

            if (is_string($car->rental_prices)) {
                $car->rental_prices = json_decode($car->rental_prices, true);
            }

            return $car;
        });
        return view('cars.index', compact('cars', 'lang'));
    }

    public function create(): Factory|Application|View
    {
        return view('cars.create');
    }

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

    public function show(Request $request, $id): Factory|Application|View
    {
        $lang = $request->query('lang', 'en');
        app()->setLocale($lang);

        $car = Car::findOrFail($id);

        // for auth
        if ($car->hidden && !auth()->check()) {
            abort(404);
        }

        return view('cars.show', compact('car'));
    }

    public function edit($id): Factory|Application|View
    {
        $car = Car::findOrFail($id);

        return view('cars.edit',  compact('car'));
    }

    public function update(UpdateCarRequest $request, Car $car): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('main_image')) {
            if ($car->main_image) {
                Storage::disk('public')->delete($car->main_image);
            }
            $validated['main_image'] = $request->file('main_image')
                ->store('cars/main', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            if ($car->gallery_images) {
                foreach ($car->gallery_images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $image) {
                $galleryPaths[] = $image->store('cars/gallery', 'public');
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        $validated['rental_prices'] = [
            '1-2' => $validated['daily_price'],
            '3-6' => $validated['daily_price'] * 0.9,
            '7+' => $validated['daily_price'] * 0.8
        ];
        unset($validated['daily_price']);

        $car->update($validated);

        return redirect()->route('cars.show', $car->id)->with('success', __('Car updated successfully!'));
    }

    public function destroy($id): RedirectResponse
    {
        $car = Car::findOrFail($id);
        $car->delete();

        return redirect()->route('cars')->with('success', 'Car deleted successfully.');
    }

    // Switch hide the car
    public function toggleVisibility(Car $car): RedirectResponse
    {
        $car->update(['hidden' => !$car->hidden]);

        return back()->with([
            'success' => 'Car visibility updated',
            'scroll_position' => request()->header('Referer') . '#car-' . $car->id
        ]);
    }
}
