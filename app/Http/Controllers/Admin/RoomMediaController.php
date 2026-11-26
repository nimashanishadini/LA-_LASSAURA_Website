<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Villa;
use App\Models\VillaMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomMediaController extends Controller
{
    public function store(Request $request, Villa $room)
    {
        $request->validate(['images' => ['required', 'array', 'min:1', 'max:10'], 'images.*' => ['bail', 'required', 'file', 'max:30720', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:max_width=8000,max_height=8000']], ['images.*.max' => 'Each room photograph must be 30 MB or smaller.', 'images.*.uploaded' => 'A photograph could not be uploaded. Try a smaller file.', 'images.max' => 'Upload no more than 10 photographs at a time.']);
        $paths = [];
        try {
            DB::transaction(function () use ($request, $room, &$paths) {
                Villa::whereKey($room->id)->lockForUpdate()->firstOrFail();
                $order = (int) $room->media()->max('sort_order');
                $hasCover = $room->media()->where('is_primary', true)->exists();
                foreach ($request->file('images') as $image) {
                    $path = $image->store('rooms/'.$room->id, 'public');
                    if (! $path) {
                        throw new \RuntimeException('Image could not be stored.');
                    }
                    $paths[] = $path;
                    $room->media()->create(['disk' => 'public', 'path' => $path, 'alt_text' => $room->name, 'sort_order' => ++$order, 'is_primary' => ! $hasCover]);
                    $hasCover = true;
                }
            });
        } catch (\Throwable $error) {
            Storage::disk('public')->delete($paths);
            throw $error;
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Images uploaded.'], 201);
        }

        return back()->with('status', 'Images uploaded.');
    }

    public function update(Request $request, Villa $room, VillaMedia $media)
    {
        abort_unless($media->villa_id === $room->id, 404);
        $data = $request->validate(['alt_text' => ['nullable', 'string', 'max:255'], 'sort_order' => ['required', 'integer', 'min:0', 'max:10000'], 'is_primary' => ['sometimes', 'boolean']]);
        DB::transaction(function () use ($room, $media, $data, $request) {
            Villa::whereKey($room->id)->lockForUpdate()->firstOrFail();
            if ($request->boolean('is_primary')) {
                $room->media()->update(['is_primary' => false]);
            }
            // An unchecked box leaves the current cover intact; another image can replace it.
            unset($data['is_primary']);
            if ($request->boolean('is_primary')) {
                $data['is_primary'] = true;
            }
            $media->update($data);
        });

        return back()->with('status', 'Image updated.');
    }

    public function destroy(Villa $room, VillaMedia $media)
    {
        abort_unless($media->villa_id === $room->id, 404);
        DB::transaction(function () use ($room, $media) {
            Villa::whereKey($room->id)->lockForUpdate()->firstOrFail();
            $cover = $media->is_primary;
            $media->delete();
            if ($cover) {
                $room->media()->first()?->update(['is_primary' => true]);
            }
        });
        Storage::disk($media->disk)->delete($media->path);

        return back()->with('status', 'Image removed.');
    }
}
