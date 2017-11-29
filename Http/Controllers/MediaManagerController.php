<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Kris\LaravelFormBuilder\FormBuilder;
use ProVision\Administration\Http\Controllers\BaseAdministrationController;
use ProVision\MediaManager\Forms\ItemForm;
use ProVision\MediaManager\Http\Requests\IndexRequest;
use ProVision\MediaManager\Http\Requests\StoreRequest;
use ProVision\MediaManager\Models\MediaManager;
use Response;

class MediaManagerController extends BaseAdministrationController {
    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request) {

        $mediaQuery = MediaManager::whereNotNull('id');

        if ($request->has('mediaable_type')) {
            $mediaQuery->where('mediaable_type', $request->mediaable_type);
        }

        if ($request->has('mediaable_sub_type')) {
            $mediaQuery->where('mediaable_sub_type', $request->mediaable_sub_type);
        } else {
            $mediaQuery->whereNull('mediaable_sub_type');
        }

        if ($request->has('mediaable_id')) {
            $mediaQuery->where('mediaable_id', $request->mediaable_id);
        }

        $items = $mediaQuery->sorted()->get();

        return view('media-manager::items', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|StoreRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request) {
        $file = $request->file('file');

        if (!$file) {
            return Response::json(['not selected file'], 422);
        }

        if (!$file->isValid()) {
            return Response::json(['Invalid file'], 422);
        }

        $media = new MediaManager();
        $media->mediaable_id = $request->input('mediaable_id');
        $media->mediaable_type = $request->mediaable_type;
        $media->user_id = Auth::guard(Config::get('provision_administration.guard'))->user()->id;

        if ($request->has('mediaable_sub_type') && !empty($request->mediaable_sub_type)) {
            $media->mediaable_sub_type = $request->mediaable_sub_type;
        }

        $media->save();

        $pathInfo = pathinfo($file->getClientOriginalName());
        $newFileName = str_slug($pathInfo['filename']) . '.' . $pathInfo['extension'];

        $media->storageDisk->put($media->path . DIRECTORY_SEPARATOR . $newFileName, $file);

        $media->file = $newFileName;
        $media->mime_type = $media->storageDisk->mimeType($media->path . $media->file);

        $media->save();

        $media->resize($media);

        return view('media-manager::item', ['item' => $media]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $media = MediaManager::findOrFail($id);
        return view('media-manager::item', ['item' => $media]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id, FormBuilder $formBuilder) {
        $media = MediaManager::findOrFail($id);
        $form = $formBuilder->create(ItemForm::class, [
                'method' => 'PUT',
                'url' => \Administration::route('media-manager.update', $media->id),
                'model' => $media,
            ]
        );

        return view('media-manager::edit', compact('form'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        if ($request->has('type')) {
            if ($request->input('type') == 'sort') {
                /*
                 * save sort
                 */
                $media = MediaManager::findOrFail($id);

                if ($request->has('before_id')) {
                    $next = MediaManager::findOrFail($request->input('before_id'));
                    $media->moveBefore($next);
                } else {
                    $prev = MediaManager::orderBy('order_index', 'desc')
                        ->where('mediaable_type', $media->mediaable_type);

                    if (!empty($media->mediaable_sub_type)) {
                        $prev->where('mediaable_sub_type', $media->mediaable_sub_type);
                    }

                    $prev->where('mediaable_id', $media->mediaable_id)
                        ->first();

                    if (!$prev) {
                        return Response::json(['Not found preview media manager object'], 500);
                    }

                    $media->moveAfter($prev);
                }

                return Response::json(['ok'], 200);
            } elseif ($request->input('type') == 'rename' && $request->has('name')) {
                /*
                 * rename file
                 */
                $pathInfo = pathinfo($request->input('name'));
                $newFileName = str_slug($pathInfo['filename']) . '.' . $pathInfo['extension'];

                $media = MediaManager::findOrFail($id);

                $files = $media->storageDisk->files($media->path);
                if ($files) {
                    //remove cached files
                    foreach ($files as $file) {
                        if (realpath($file) != realpath($media->path . $media->file)) {
                            $media->storageDisk->delete($file);
                        }
                    }
                    //rename original file
                    $media->storageDisk->move(realpath($media->path . $media->file), $media->path . $newFileName);
                    $media->file = $newFileName;
                    $media->save();
                    $media->quickResize();

                    return Response::json(['ok'], 200);
                }
                return Response::json(['Files not found!'], 422);
            } elseif ($request->input('type') == 'update') {

                $media = MediaManager::findOrFail($id);

                $data = $request->except([
                    'type',
                    '_method',
                    '_token'
                ]);

                //set visible  = false if unset
                foreach ($data as $locale => $value) {
                    if (!isset($data[$locale]['visible']) || empty($data[$locale]['visible'])) {
                        $data[$locale]['visible'] = false;
                    }
                }

                $media->fill($data);
                $media->save();

                return Response::json(['ok'], 200);
            }
        }

        return Response::json(['Unknown update action'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        if (\Request::has('checked')) {
            foreach (\Request::input('checked') as $id) {
                $media = MediaManager::findOrFail($id);
                $media->delete();
            }

            return \Response::json(\Request::input('checked'), 200);
        }

        $media = MediaManager::findOrFail($id);
        $media->delete();

        return \Response::json(['ok'], 200);
    }
}
