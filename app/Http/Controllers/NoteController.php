<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NoteController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $archived = $request->has('archived')
            ? $request->boolean('archived')
            : false; // ← default active only

        $notes = Note::query()
            ->forUser($request->user())
            ->archivedState($archived)
            ->search($request->query('search'))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return NoteResource::collection($notes);
    }

    public function store(StoreNoteRequest $request): NoteResource
    {
        $note = $request->user()->notes()->create(
            $request->validated()
        );

        return new NoteResource($note);
    }

    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
        $note->update($request->validated());

        return new NoteResource($note);
    }

    public function archive(Note $note): NoteResource
    {
        // Authorization handled here intentionally: no input, non-CRUD action
        $this->authorize('archive', $note);

        $note->archive();

        return new NoteResource($note);
    }
}
