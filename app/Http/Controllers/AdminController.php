<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Locale;

class AdminController extends Controller
{
    public function index(){
        if (!session('admin_logged_in')) {
            return redirect()->route('login.file');
        }

        $locales = DB::table('locales')->get(['locale', 'language']);
        
        $columns = array_merge(Schema::getColumnListing('original_words'), Schema::getColumnListing('translated_words'));
        
        $selected = session('selected'); 
        $limit = session('limit', 10);

        return view('Admin', compact('locales', 'columns', 'selected', 'limit'));
        }
    //select rows
    public function select(Request $request){

        $columns = array_merge(Schema::getColumnListing('original_words'), Schema::getColumnListing('translated_words'));

        $whereKey = $request->input("search_select_where_key");
        $whereValue = $request->input("search_select_where_value");
        $limit = (int)$request->input("search_select_limit", 10);

        if ($limit <= 0) $limit = 10;
        $query = DB::table('original_words')
        ->leftJoin('translated_words', 'original_words.id', '=', 'translated_words.original_id')
        ->select('translated_words.id as id', 'original_words.key as Ключ', 'original_words.value as Оригинал', 'translated_words.locale as Локаль', 
        'translated_words.meaning as Перевод', 'translated_words.check_required as Требуется проверка',)
        ;

        

        if ($whereKey && $whereValue && in_array($whereKey, $columns)) {
            $query->where($whereKey, $whereValue);
        }

        $perPage = ($limit > 100) ? 100 : $limit;
        $selected = $query->paginate($perPage)->appends($request->all());

        if(request()->ajax()){
            return view('Table', compact('selected', 'limit'))->render();
        }
        return redirect()->route('admin.index')->with(['selected' => $selected, 'limit' => $limit, 'old_input' => $request->all() 
        ]);          
    }
    

    //add_locale
    public function addLocale(Request $request){

        $locale = $request->input('search_add_locale');

        $language = strtolower($request->input('new_language'));

        if ($locale && $language){
            DB::table('locales')->insert(['locale' => $locale, 'language' => $language]);
            return response()->json(['message' => "Язык $language добавлен"]);
        }
    }

    //deelete a language (row)
    public function deleteLocale(Request $request){
        $locale = $request->input('search_delete_locale');
        DB::table('locales')->where('locale', $locale)->delete();
        return response()->json(['message' => "Язык $locale удалён"]);
    }

    //update a row
    public function update(Request $request)
    {
        $columnToUpdate = $request->input('search_update_column');
        $newValue = $request->input('search_update');
        $whereKey = $request->input('search_update_where_key');
        $whereValue = $request->input('search_update_where_value');


        if ($whereKey && $whereValue && $columnToUpdate) {
        $originalCols = Schema::getColumnListing('original_words');
        $translatedCols = Schema::getColumnListing('translated_words');

        $targetTable = in_array($columnToUpdate, $originalCols) ? 'original_words' : 'translated_words';
      
        $searchTable = (str_contains($whereKey, 'translated_words') || in_array($whereKey, $translatedCols)) 
                        ? 'translated_words' : 'original_words';

        if ($targetTable === $searchTable) {
            $updated = DB::table($targetTable)->where($whereKey, $whereValue)->update([$columnToUpdate => $newValue]);
        } else {
            if ($searchTable === 'translated_words') {
                $originalId = DB::table('translated_words')->where($whereKey, $whereValue)->value('original_id');
                $updated = DB::table('original_words')->where('id', $originalId)->update([$columnToUpdate => $newValue]);
            } else {
                $id = DB::table('original_words')->where($whereKey, $whereValue)->value('id');
                $updated = DB::table('translated_words')->where('original_id', $id)->update([$columnToUpdate => $newValue]);
            }
        }
        
        if ($updated) {
            return response()->json(['message' => "Запись успешно обновлена!"]);
        }
        return response()->json(['message' => "Запись не найдена или данные идентичны"], 404);
        }
        return response()->json(['message' => "Заполните все поля для поиска"], 400);

    }

    public function delete(Request $request){
        $column = $request->input('search_delete_column');
        $value = $request->input('search_delete');

        $originalColumns = Schema::getColumnListing('original_words');

        if (in_array($column, $originalColumns)) {
            $deleted = DB::table('original_words')->where($column, $value)->delete();
            //слово и все его переводы  удалены
        } 
        else {
            // Удаляем только конкретную строку из таблицы переводов
            $deleted = DB::table('translated_words')->where($column, $value)->delete();
            //конкретный первод удален, основное слово сохранено
        }

        if ($deleted) {
            return response()->json(['message' => "Значение удалено"]);
        }
        
    }

    //manual insert rows
    public function manualInsert(Request $request){
        $lines = $request->input('lines');
        
        $line0 = $lines[0];
        $key = $line0['value'];
        $line1 = $lines[1];
        $locale = $line1['value'];
        $line2 = $lines[2];
        $meaning = $line2['value'];
        
            DB::table('original_words')->updateOrInsert(
                ['key' => $key],
                ($locale == 'en') ? ['value' => $meaning] : []
            );

            $original_id = DB::table('original_words')->where('key', $key)->value('id');

            if ($locale != 'en' && $original_id){
                DB::table('translated_words')->updateOrInsert(
                    [
                        'original_id' => $original_id, 'locale' => $locale
                    ],
                    [
                        'meaning' => $meaning
                    ]
                );
            }

        
        
        return response()->json(['data' => $lines]);
    }

    //insert rows
    public function insert(Request $request)
    {

        set_time_limit(600);
        if ($request->isMethod('post')) {
            if (!request()->hasFile('filename')) {
                return back()->withErrors(['filename' => 'File has not been uploaded.']);

            }
            $file = $request->file('filename');
            if (!$file->isValid()) {
                return back()->withErrors(['filename' => 'File is not valid.']);
            }

            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();


            $allowedExtensions = ["json", "csv", "po", "txt", "ini"];

            if (!in_array($extension, $allowedExtensions)) {
                return back()->withErrors(['extension' => 'File type is not allowed. Please check which types are allowed.']);
            }

            if ($size > 1024 * 1024 * 10) {
                return back()->withErrors(['size' => 'File is too big. Please upload less than 10MB.']);
            }
            $lang = $request->input('lang', 'en');

            $words = $this->parseFileContent($file);

            if (empty($words)) {
                return back()->withErrors(['filename' => 'Файл пуст или не удалось распознать данные.']);
            }

            $this->insertByLanguage($words, $lang);


            return back()->with('success', 'Слова добавлены для языка: ' . $lang);
        }
        return view('admin');
    }

    private function parseFileContent($file)
    {
        $extension = $file->getClientOriginalExtension();
        $path = $file->getRealPath();
        $data = [];


        if ($extension == 'json') {
            $data_json = json_decode(file_get_contents($path), true);
            if ($data_json) {
                foreach ($data_json as $key => $value) {
                    $data[$key] = $value;
                }
            }
        }

        if ($extension == 'po') {
            $lines = file($path, FILE_IGNORE_NEW_LINES);
            $currentKey = '';
            foreach ($lines as $line) {

                if (str_starts_with($line, "msgid")) {
                    $currentKey = trim(str_replace(['msgid', '"'], '', $line));
                }
                if (str_starts_with($line, "msgstr")) {
                    $value = trim(str_replace(['msgstr', '"'], '', $line));
                    if ($value != '') {
                        $data[$currentKey] = $value;
                    }
                }

            }
        }

        if ($extension == 'ini') {
            $lines = file($path, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                if (str_contains($line, "=")) {
                    $separated = explode("=", $line, 2);
                    $data[trim($separated[0])] = trim($separated[1]);
                }
            }
        }

        if ($extension == 'csv') {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                while (($line = fgetcsv($handle, 10000, ',')) !== false) {
                    if (isset($line[0]) && isset($line[1])) {
                        $data[trim($line[0])] = trim($line[1]);
                    }
                }
            }
            fclose($handle);
        }


        return $data;

    }

    private function insertByLanguage($words, $lang)
    {   
        $locales = DB::table('locales')->pluck('locale')->toArray();
        if (!in_array($lang, $locales)) return false;

        DB::transaction(function () use ($words, $lang){
        foreach ($words as $key => $value) {
            $updateData = ($lang == 'en') ? ['value' => $value] : [];
            DB::table('original_words')->updateOrInsert(
                    ['key' => $key],
                    $updateData
                );
            
                $original_id = DB::table('original_words')->where('key', $key)->value('id');
               
                if ($lang != 'en'){
                    DB::table('translated_words')->updateOrInsert(
                    ['original_id' => $original_id,
                    'locale' => $lang],
                    ['meaning' => $value],
                );
             }  
        }
    });
        return true;
    }
}
