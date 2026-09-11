<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Event;
use App\Models\Question;

class FormTicketController extends Controller {
    public function index($event_id) {
        $event = Event::find($event_id);
        return Inertia::render('Customer/Event/FormTicket', [
            'event' => $event
        ]);
    }

    public function getQuestions(Request $request) {
        try {
            $questions = Question::with(['tickets:id,name'])->where('event_id', $request->event_id)->get();
            return ResponseTrait::response(null, $questions);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }

    public function createQuestion(Request $request) {
        try {
            $options = null;
            if ($request->typeInput === 'select') {
                for ($i = 0; $i < sizeof($request->options); $i++) { 
                    $options .= str_replace(',', '', $request->options[$i]['label']).',';
                }
                $options = trim($options, ',');
            }
            $question = Question::create([
                'event_id'    => $request->event_id,
                'title'       => $request->title,
                'information' => $request->placeholder,
                'required'    => $request->required,
                'type'        => $request->typeInput,
                'options'     => $options
            ]);

            $question->tickets()->sync($request->tickets);
            return ResponseTrait::response('La forma por boleto se creó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }

    public function editQuestion(Request $request) {
        try {
            $options = null;
            if ($request->typeInput === 'select') {
                for ($i = 0; $i < sizeof($request->options); $i++) { 
                    $options .= str_replace(',', '', $request->options[$i]['label']).',';
                }
                $options = trim($options, ',');
            }
            $question              = Question::find($request->id);
            $question->title       = $request->title;
            $question->information = $request->placeholder;
            $question->required    = $request->required;
            $question->type        = $request->typeInput;
            $question->options     = $options;
            $question->save();

            $question->tickets()->sync($request->tickets);

            return ResponseTrait::response('La forma por boleto se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }

    public function changeStatusQuestion($id) {
        try {
            $question         = Question::find($id);
            $question->active = !$question->active;
            $question->save();

            $txt = $question->active ? 'activó' : 'desactivó';
            return ResponseTrait::response("El campo se $txt correctamente.");
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }
}
