<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AppLing — Перевод языковых файлов</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>

<header>
    <p>AppLing</p>
</header>

<div class="container">
    <div class="card">
        <form action="{{ route('search.file') }}" method="post" id="search_form" class="search_form">
            @csrf
            <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)">
                <label class="section-title">Быстрый поиск</label>
                <div class="icon-wrapper" style="position: relative; display: flex;">
                    <img src="{{ asset('images/question.png') }}" alt="Информация" style="height: 24px; width: 24px; position: relative">
                    <div class="info" style="display: none">
                        Введите слово/фразу, которое собираетесь перевести, затем укажите языка этого слова, и на какой язык нужно перевести.
                        После этого нажмите кнопку "Поиск", и система найдет для вас перевод, если он существует.
                    </div>
                </div>
            </div>
            <div class="suggestions" style="position: relative">
                <input type="search" name="word_to_translate" placeholder="Введите слово для перевода" style="margin-bottom: 0;">
                <div id="suggestions-box"></div>
            </div>
            <label class="section-title" style="margin-top: 15px">Перевести с языка:
                <select name="search_lang_from" id="search_languages_from" style="margin-top: 15px;">
                    @isset($locales)
                        @foreach($locales as $row)
                                <option value="{{ $row->locale }}">{{ $row->language}}</option>
                        @endforeach
                    @endisset
            </select>
            </label>
            <label class="section-title">Перевести на язык:
                <select name="search_lang_to" id="search_languages_to" style="margin-top: 15px;">
                    @isset($locales)
                    <option value="ru">Русский</option>
                        @foreach($locales as $row)
                                <option value="{{ $row->locale }}">{{ $row->language}}</option>
                        @endforeach
                    @endisset
                </select>
            </label>
            <div id="search-result" style="margin: 10px; font-weight: bold; color: green;">
            </div>
            <button type="submit" id="search-button">Поиск</button>
        </form>
    </div>

    <div class="card">
        <form class="search_form" action="{{ route('upload.file') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)">
                <label class="section-title">Перевод языкового файла:</label>
                <div class="icon-wrapper" style="position: relative; display: flex;">
                    <img src="{{ asset('images/question.png') }}" alt="Информация" style="height: 24px; width: 24px; position: relative;">
                    <div class="info" style="display: none;">
                        Загрузите языковой файл, содержащий строки со словами и фразами, в нужном формате (список поддерживаемых форматов смотрите ниже).
                        Выберите, с какого языка и на какой нужно перевести Ваш файл. Нажмите кнопку "Перевести файл". Подождите некоторое время, пока
                        система найдет для Вас переводы. Затем Вы получите новый файл с готовыми строками на выбранном Вами целевом языке.
                        Вы можете безопасно скачать его на свой компьютер (рекомендуется скачать его в ту же папку, где хранится файл с оригинальным языком).
                    </div>
                </div>
            </div>
            <label class="section-title" style="margin-top: 15px">Перевести с языка:
                <select name="lang_from" id="languages_from">
                    @isset($locales)
                        @foreach($locales as $row)
                                <option value="{{ $row->locale }}">{{ $row->language}}</option>
                        @endforeach
                    @endisset
                </select>
            </label>

            <label class="section-title">Перевести на язык:
                <select name="lang_to" id="languages_to">
                    @isset($locales)
                        <option value="ru">Русский</option>
                        @foreach($locales as $row)
                                <option value="{{ $row->locale }}">{{ $row->language}}</option>
                        @endforeach
                    @endisset
                </select>
            </label>

            <div class="file-upload-group">
                <input type="hidden" name="MAX_FILE_SIZE" value="300000" />
                <input name="filename" type="file">
                <p class="hint">Выберите языковой файл</p><br>
                <div class="text_allowed_extensions">
                    <div onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
                        <p  style="font-size: 0.76rem; font-style:italic">Список поддерживаемых форматов</p>
                        <div class="info">
                            .json, .ini, .csv, .po
                        </div>
                    </div>
                </div>
{{--                <select name="extensions" id="extensions">--}}
{{--                    <option value="json">json</option>--}}
{{--                    <option value="csv">csv</option>--}}
{{--                    <option value="po">po</option>--}}
{{--                    <option value="tsv">tsv</option>--}}
{{--                    <option value="txt">txt</option>--}}
{{--                    <option value="php">php</option>--}}
{{--                    <option value="ini">ini</option>--}}
{{--                    <option value="tbx">tbx</option>--}}
{{--                    <option value="docx">docx</option>--}}
{{--                    <option value="xls">xls</option>--}}
{{--                    <option value="xlsx">xlsx</option>--}}
{{--                </select>--}}
            </div>

            <input type="submit" value="Перевести файл">
        </form>
    </div>

        @if($errors->any())
            <div style="color: red;">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </div>
    @if(session('wordList'))
        @if(session('translated_filed'))
            <div class="card">
                <label class="section-title" style="color: #27ae60;">Файл готов!</label>
                <p>Ваш перевод сохранен как: <strong>{{ session('translated_filed') }}</strong></p>
                <br>
                <a class="download_file" href="{{asset('storage/' . session('translated_filed'))}}" download="{{ session('translated_filed') }}">
                    <button>Скачать файл</button>
                </a>
            </div>
        @endif
    @endif
</div>

<footer>
    &copy; 2026 AppL10N Service
</footer>

</body>
</html>
<script>
    const input = document.getElementsByName('word_to_translate')[0];
    const suggestionsBox = document.getElementById('suggestions-box');
    const langSelect = document.getElementById('search_languages_from');

    input.addEventListener('input', function (){
        const query = this.value;
        const lang = langSelect.value;

        if (query.length < 2){
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch(`/search/suggest?term=${query}&lang=${lang}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    suggestionsBox.innerHTML = data.map(word =>
                    `<div class="suggest-item">${word}</div>`).join('');
                    suggestionsBox.style.display = 'block';
                }else{
                    suggestionsBox.style.display = 'none';
                }
            });
    })

    suggestionsBox.addEventListener('click', function (e) {
        if (e.target.classList.contains('suggest-item')){
            input.value = e.target.innerText;
            suggestionsBox.style.display = 'none';
        }
    });
    document.addEventListener('click', function (e){
        if (e.target !== suggestionsBox){
            suggestionsBox.style.display = 'none';
        }
    });
    document.getElementById('search_form').addEventListener('submit', function (e){
        e.preventDefault();
        const form = this;
        const result = document.getElementById('search-result');
        const search_button = document.getElementById('search-button');
        const formData = new FormData(form);

        search_button.disabled = true;
        search_button.innerText = 'Идёт поиск...';

        fetch("{{ route('search.file') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                search_button.disabled = false;
                search_button.innerText = 'Поиск';
                data_translated_word = data.translated_word;

                if (data_translated_word){
                result.innerText = data_translated_word.charAt(0).toUpperCase()+data_translated_word.slice(1);
                result.style.color = 'green';
                result.style.display = 'block';
            }else if (data.error) {
                    result.innerText = data.error;
                    result.style.display = 'block';
                    result.style.color = 'red';
                }

    })
            .catch(error => {
                console.error('Ошибка:', error);
                search_button.disabled = false;
                search_button.innerText = 'Поиск';
            });
    });
</script>
<script>
    function showInfo(element) {
        let infoBox = element.getElementsByClassName('info')[0];

        document.querySelectorAll('.info').forEach(el => {
            if (el !== infoBox) {
                el.style.display = 'none';
            }
        });

        infoBox.style.display = 'block';
    }

    function hideInfo(element) {
        let infoBox = element.getElementsByClassName('info')[0];

        if (infoBox && infoBox.classList.contains('info')) {
            infoBox.style.display = 'none';
        }
    }
</script>

