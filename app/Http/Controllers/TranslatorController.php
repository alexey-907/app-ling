<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class TranslatorController extends Controller
{
    public function index(){
        if (!session('translator_logged_in')) {
            return redirect()->route('login.file');
        }
        
        $words_columns = array_merge(Schema::getColumnListing('original_words'), Schema::getColumnListing('translated_words'));
        $unchecked_words_columns = Schema::getColumnListing('unchecked_words');

        $selected = session('selected'); 
        $limit = session('limit', 10);
        $table_name = session('table_name');
        return view('Translator', compact(['words_columns', 'unchecked_words_columns', 'selected', 'limit', 'table_name']));
    }
    //select rows
    public function select(Request $request){
        $table_name = $request->input('table_name');
        $words_columns = array_merge(Schema::getColumnListing('original_words'), Schema::getColumnListing('translated_words'));

        $unchecked_words_columns = Schema::getColumnListing('unchecked_words');
       
       
        if ($table_name == 'all_words') {
            $query = DB::table('original_words')
            ->leftJoin('translated_words', 'original_words.id', '=', 'translated_words.original_id')
            ->select('translated_words.id as id', 'original_words.key as Ключ', 'original_words.value as Оригинал', 'translated_words.locale as Локаль', 
            'translated_words.meaning as Перевод', 'translated_words.check_required as Требуется проверка',)
            ;
        } else{
            $query = DB::table('unchecked_words');
        }

        $column = $request->input("search_select_column", "*");
        $whereKey = $request->input("search_select_where_key");
        $whereValue = $request->input("search_select_where_value");
        $limit = (int)$request->input("search_select_limit", 10);

        if ($limit <= 0) $limit = 10;

        if ($column !== '*' && in_array($column, $unchecked_words_columns) || in_array($column, $words_columns)) {
            $query->select($column);
        }

        if ($whereKey && $whereValue) {
            $query->where($whereKey, $whereValue);
        }

        $perPage = ($limit > 100) ? 100 : $limit;
        $selected = $query->paginate($perPage)->appends($request->all());

        if(request()->ajax()){
            return view('table', compact('selected', 'limit', 'table_name'))->render();
        }

        return redirect()->route('translator.index')->with(['selected' => $selected,  
        'limit' => $limit, 'table_name' => $table_name, 'old_input' => $request->all(), 
        'words_columns' => $words_columns, 'unchecked_words_columns' => $unchecked_words_columns]);  
    }
    //update a row
    public function update(Request $request)
    {
        $columnToUpdate = $request->input('search_update_column');
        $newValue = $request->input('search_update');
        $whereKey = $request->input('search_update_where_key');
        $whereValue = $request->input('search_update_where_value');


        if ($whereKey && $whereValue){
            //table: unchecked_words
            $row = DB::table('unchecked_words')->where($whereKey, $whereValue)->first();
            $updated_unchecked = DB::table('unchecked_words')->where($whereKey, $whereValue)->update(['word' => $newValue]);

            //table: translated_words
            $id_original = $row->word_id;
            $locale = $row->locale;
            $updated_words = DB::table('translated_words')->where('original_id', $id_original)->where('locale', $locale)->updateOrInsert(['meaning' => $newValue, 'check_required' => '']);

            if ($updated_unchecked && $updated_words) {
                return response()->json(['message' => "Запись успешно обновлена!"]);
            }
            return response()->json(['message' => "Запись не найдена или данные идентичны"], 404);
        }
        return response()->json(['message' => "Заполните все поля для поиска"], 400);

    }


}
