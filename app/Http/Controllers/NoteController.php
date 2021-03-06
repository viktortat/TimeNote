<?php

namespace TimeNote\Http\Controllers;

use TimeNote\Note;
use TimeNote\Helpers\Routines;
use TimeNote\Http\Requests\StoreNote;
use Illuminate\Http\Request;


class NoteController extends Controller
{
    /**
     * @param $hash
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($hash)
    {
        Note::deleteOld();

        $note = Routines::getNoteByHash($hash);
        if ($note) {
            if (time() > strtotime($note->time_show)) {
                $note->message = decrypt($note->message);
                $note->increment('viewed');
                return view('show', compact('note'));
            }else {
                return view('errors.app',
                    ['message' => __('messages.error_not_now',
                        ['time' => Routines::formatDate($note->time_show)]
                    )]);
            }
        } else {
            return view('errors.app', ['message' => __('messages.error_hash_not_found')]);
        }
    }

    /**
     * @param StoreNote $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function store(StoreNote $request)
    {
        $newNote = new Note;

        $newNote->time_show = $request->date;
        $newNote->message = encrypt($request->text);
        $newNote->hash = Routines::makeHash($request->text);
        $newNote->ip = request()->ip();

        $newNote->save();

        return view('success',[
            'hash_url' => url('/') . '/box/' . $newNote->hash,
            'time_show' => Routines::formatDate($request->date),
        ]);
    }
}
