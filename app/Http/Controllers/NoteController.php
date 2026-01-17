<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Resources\NoteResource;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request)
    {
        $note = $request->user()->notes()->create(
            $request->validated()
        );

        return new NoteResource($note);
    }
}
