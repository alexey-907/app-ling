<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class SearchController extends Controller
{
    public function search(Request $request)
    {
        $fileController = new \App\Http\Controllers\FileUploadController();

        $lang_from = $request->input('search_lang_from');
        $lang_to = $request->input('search_lang_to');
        $word_to_translate = $request->input('word_to_translate');

        if ($lang_from == 'en') {
            $originalId = DB::table('original_words')->where('value', $word_to_translate)->value('id');
        } else {
            $originalId = DB::table('translated_words')->where('locale', $lang_from)->where('meaning', $word_to_translate)->value('original_id');
        }
        if ($originalId) {
            if ($lang_to == 'en') {
                $result = DB::table('original_words')->where('id', $originalId)->value('value');
            } else {
                $result = DB::table('translated_words')->where('original_id', $originalId)->where('locale', $lang_to)->value('meaning');
            }
        }else{
            $translatedArray = $fileController->translateWithApi([$word_to_translate], $lang_from, $lang_to, false);
            if (empty($translatedArray)) {
                return back()->withErrors(['not_found' => 'Перевод не найден']);
            }
            $result = reset($translatedArray);
        }


        if ($request->ajax()){
            return response()->json([
                'translated_word' => $result ?? null,
                'error' => isset($result) ? null : 'Перевод не найден',
            ]);
        }
        return back()->with(['translated_word'=> $result, 'lang_from' => $lang_from, 'lang_to' => $lang_to, 'word_to_translate' => $word_to_translate]);
    }

    public function suggest(Request $request)
    {
        $term = $request->input('term');
        $lang = $request->input('lang');

        if (empty($term) || strlen($term) < 2) return response()->json([]);

        
            $suggestions = DB::table('original_words')->where('value', 'LIKE', $term . '%')->limit(5)->pluck('value');
        
        if ($lang != 'en'){
        $suggestions = DB::table('translated_words')->where('meaning', 'LIKE', $term . '%')->where('locale', $lang)->limit(5)->pluck('meaning');
        }
        return response()->json($suggestions);
    }
}
