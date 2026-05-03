<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Stichoza\GoogleTranslate\GoogleTranslate;


class FileUploadController extends Controller
{
    public function index(){
        $locales = DB::table('locales')->get(['locale', 'language']);
        return view('Main', compact('locales'));
    }

    public function uploadFile(Request $request)
    {
        set_time_limit(300);
        $lang_from = $request->input('lang_from', 'en');
        $lang_to = $request->input('lang_to', 'en');

        if ($request->isMethod('post')) {
            if (!request()->hasFile('filename')) {
                return back()->withErrors(['filename' => 'File has not been uploaded.']);

            }
            $file = $request->file('filename');

            if (!$file->isValid()) {
                return back()->withErrors(['filename' => 'File is not valid.']);
            }

            $name = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();
            $path = $file->getRealPath();

            $allowedExtensions = ["json", "csv", "po", "txt", "ini"];

            if (!in_array($extension, $allowedExtensions)) {
                return back()->withErrors(['extension' => 'File type is not allowed. Please check which types are allowed.']);
            }

            if ($size > 1024 * 1024 * 10) {
                return back()->withErrors(['size' => 'File is too big. Please upload less than 10MB.']);
            }

            $changedFile = uniqid() . '.' . $extension;
            $newFilePath = $file->storeAs('uploads/language_files', $changedFile);

            $wordList = [];
            if ($extension == 'json') {
                $data_json = json_decode(file_get_contents($path), true);
                if ($data_json) {
                    foreach ($data_json as $key => $value) {
                        $wordList[$key] = $value;
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
                            $wordList[$currentKey] = $value;
                        }
                    }

                }
            }

            if ($extension == 'ini') {
                $lines = file($path, FILE_IGNORE_NEW_LINES);
                foreach ($lines as $line) {
                    if (str_contains($line, "=")) {
                        $separated = explode("=", $line, 2);
                        $wordList[trim($separated[0])] = trim($separated[1]);
                    }
                }
            }

            if ($extension == 'csv') {
                if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                    while (($line = fgetcsv($handle, 10000, ',')) !== false) {
                        if (isset($line[0]) && isset($line[1])) {
                            $wordList[trim($line[0])] = trim($line[1]);
                        }
                    }
                }
                fclose($handle);
            }
        }

        $translations = $this->select($wordList, $lang_from, $lang_to);

        $jsonResult = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $readyWords = [];
        foreach ($translations as $key => $value) {
            $readyWords[] = [
                'key' => $key,
                'value' => $value,
            ];
        };

        $originalContent = file_get_contents($path);
        $translatedFile = $this->makeFile($readyWords, $extension, $name, $lang_to, $originalContent);

        return redirect()->route('main')->with([
            'wordList' => $readyWords,
            'translated_filed' => $translatedFile,
            'status' => 'Файл успешно переведен на язык: ' . $lang_to
        ]);

    }

    private function makeFile($readyWords, $extension, $name,  $lang_to, $originalContent)
    {
        $originalName = $name;
        $translatedLang = $lang_to;

        $all_langs = ['en', 'ru', 'fr', 'it', 'es', 'de'];
        $cleanName = str_replace($all_langs, '', pathinfo($name, PATHINFO_FILENAME));
        $changedName = trim($cleanName, '._-') . '_' . $lang_to . '.' . $extension;

        $contentToPut = [];
        foreach ($readyWords as $item) {
            $contentToPut[$item['key']] = $item['value'];
        }

        if ($extension == 'ini') {
            $fileData = $this->formatToIni($contentToPut, $translatedLang);
        };
        if ($extension == 'json') {
            $fileData = $this->formatToJson($contentToPut, $translatedLang);
        };
        if ($extension == 'po') {
            $fileData = $this->formatToPo($contentToPut, $translatedLang);
        };
        if ($extension == 'csv') {
            $fileData = $this->formatToCsv($contentToPut, $translatedLang);
        };

        Storage::disk('public')->put($changedName, $fileData);

        return $changedName;
    }

    private function formatToJson($translations, $lang_to)
    {
        return json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    private function formatToIni($translations, $lang_to)
    {
        $res = "";
        foreach ($translations as $k => $v) {
            $res .= "$k = $v\n";
        }
        return $res;
    }
    private function formatToPo($translations, $lang_to)
    {
        $res = "msgid \"\"\nmsgstr \"\"\n\"Language: $lang_to\{\n}\n\"Content-Type: text/plain; charset=UTF-8\\n\"\n\n";
        foreach ($translations as $k => $v) $res .= "msgid \"$k\"\nmsgstr \"$v\"\n\n";
        return $res;
    }
    private function formatToCsv($translations, $lang_to)
    {

        $res = "";
        foreach ($translations as $k => $v) {
            $res .= "$k,$v\n";
        }
        //BOM - Byte Order Mark (чтобы акценты воспринимались
        return "\xEF\xBB\xBF" . $res;
    }
    private function select($wordList, $lang_from, $lang_to)
    {
        $translatedWords = [];
        $toTranslate = [];
        $columns = DB::table('locales')->pluck('locale')->toArray();
        
        if (!in_array($lang_from, $columns) || !in_array($lang_to, $columns)) return false;

        foreach ($wordList as $key => $value) {
            $value = trim($value);
            $meaning = null;

            if ($lang_from == 'en') {
            $originalId = DB::table('original_words')->where('value', $value)->value('id');
        } else {
            $originalId = DB::table('translated_words')->where('locale', $lang_from)->where('meaning', $value)->value('original_id');
        }

         if ($originalId) {
            if ($lang_to == 'en') {
                $meaning = DB::table('original_words')->where('id', $originalId)->value('value');
            } else {
                $meaning = DB::table('translated_words')->where('original_id', $originalId)->where('locale', $lang_to)->value('meaning');
            }
        }

            if ($meaning){
                $translatedWords[$key] = $meaning;
            } else{
                //API!!!!!!!!!!!!!!!!!!1
                $toTranslate[$key] = $value;
            }
        };

        if (!empty($toTranslate)) {
            $apiResults = $this->translateWithApi($toTranslate, $lang_from, $lang_to, true);

            if ($apiResults) {
                $i = 0;
                foreach ($apiResults as $key => $value) {
                    $translatedWords[$key] = $apiResults[$i] ?? $value;
                    $i++;
                }
            }
        }
        // \Log::info('res:', array ($wordList, $translatedWords, $toTranslate));
        return $translatedWords;
    }
    public function translateWithApi($toTranslate, $lang_from, $lang_to, $toInsert) {
        $options = [
            'verify' => false,
            'timeout' => 60,
        ];

        $tr = new GoogleTranslate();
        $tr->setOptions($options);
        $tr->setSource($lang_from);
        $tr->setTarget($lang_to);

        
        $separator = ' ||| ';
        $maxChars = 4500;

        $results = [];

        $currentGroup = [];
        $currentLength = 0;

        $groups = [];

        //чтобы количесвто символов, отправляемых API, не превышало 5000
        foreach ($toTranslate as $key => $value){
            $wordLength = mb_strlen($value . $separator);

            if ($currentLength + $wordLength > $maxChars && !empty($currentGroup)){
                $groups[] = $currentGroup;
                $currentGroup = [];
                $currentLength = 0;
            }

            $currentGroup[$key] = $value;
            $currentLength += $wordLength;
        }

        if (!empty($currentGroup)) {
            $groups[] = $currentGroup;
        }
        foreach ($groups as $group){
            try {
                $arrayToString = implode($separator, $group); //тольк значения

                $translatedString = $tr->translate($arrayToString);

                $translatedArray = explode($separator, $translatedString);

            
                $keys = array_keys($group);
                foreach ($keys as $index => $key) {
                    $results[$key] = $translatedArray[$index] ?? $group[$key];
                }

            } catch (\Exception $e) {
                    // \Log::error("Ошибка перевода строки '$value': " . $e->getMessage());
                    foreach($group as $key => $value){
                        $results[$key] = $value;
                    }
            }
        }
        // \Log::info('res:', array ($results));

        if ($toInsert) {
                    $this->insertIntoDB($results, $toTranslate, $lang_from, $lang_to);
                }
        return $results;
    }
    private function insertIntoDB($results, $toTranslate, $lang_from, $lang_to)
    {
        $columns = DB::table('locales')->pluck('locale')->toArray();
        if (!in_array($lang_from, $columns) || !in_array($lang_to, $columns)) return false;


        foreach ($toTranslate as $key => $value_from) {
            $value_to = $results[$key] ?? null;
            if (!$value_to) continue;

            $originalUpdate = ($lang_from == 'en') ? ['value' => $value_from] : [];
            DB::table('original_words')->updateOrInsert(
                ['key' => $key],
                $originalUpdate
            );

            $id = DB::table('original_words')->where('key', $key)->value('id');


            if ($lang_from != 'en'){
                DB::table('translated_words')->insert(
                    ['original_id' => $id, 'locale' => $lang_from, 'meaning' => $value_from]
                );
            }

            if ($lang_to != 'en'){
                DB::table('translated_words')->updateOrInsert(
                    ['original_id' => $id, 'locale' => $lang_to, 'meaning' => $value_to, 'check_required' => 1 ]
                );
                DB::table('unchecked_words')->updateOrInsert(
                    ['word_id' => $id, 'locale' => $lang_to],
                    ['locale' => $lang_to, 'word' => $value_to, 'en' => DB::table('original_words')->where('id', $id)->value('value')]
                );
            }
            

        }
        return true;
    }
}



