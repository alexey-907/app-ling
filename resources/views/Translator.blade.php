<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AppLing — Перевод языковых файлов</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css?v=1.42') }}">

</head>
<body>
<header>
    <p>AppLing</p>
</header>
<div class="container">

    {{--    из ьаблицы words--}}
    <div class="admin-row" style="margin-bottom: 0;">
        <label class="section-title">Выбор всех записей</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Чтобы выбрать строки из базы данных, нужно выбрать, какие поля отобразить (если не указать поле, будут выбраны все поля); 
                указать условие, по которому будет(-ут) выбрана(-ы) строка(-и) ({поле} имеет {значение});
                а также лимит - количество выводимых строк на экран (по умолчанию лимит равен 10)
            </div>
        </div>
        <div class="input-wrapper">
            <div class="group">
                <select name="search_select_column">
                    <option value="*">Все колонки</option>
                    @isset($words_columns)
                        @foreach($words_columns as $col)
                            @if (!in_array($col, ['id', 'original_id', 'locale', 'check_required'])) 
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endif
                        @endforeach
                    @endisset
                </select>
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">что выбираем</p>
            </div>
            <div class="group">
                <select name="search_select_where_key">
                    <option value="">поле</option>
                    @isset($words_columns)
                    <option value="translated_words.id">id перевода</option>
                        @foreach($words_columns as $col)
                            @if (!in_array($col, ['original_id', 'id'])) 
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endif
                        @endforeach
                    @endisset
                </select>
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">условие: где ...</p>
            </div>
            <div class="group">
                <input type="search" name="search_select_where_value" placeholder="значение" >
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">равно</p>
            </div>
            <div class="group">
                <input type="number" name="search_select_limit" placeholder="10" style="max-width: 100px;">
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">Лимит</p>
            </div>
        </div>
        <button value="all_words" name='table_name' onclick="selectFromDB(this)">Выбрать</button>

    </div>

    <div id="table-wrapper" style="margin-top:0;">
        @isset($selected)
            @if($table_name === 'all_words')
                @include('table')
            @endif
        @endisset
    </div>

{{--    из ьаблицы uncheked_words--}}
    <div class="admin-row" style="margin-bottom: 0;">
        <label class="section-title">Выбор непроверенных записей</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Чтобы выбрать строки из базы данных, нужно выбрать, какие поля отобразить (если не указать поле, будут выбраны все поля); 
                указать условие, по которому будет(-ут) выбрана(-ы) строка(-и) ({поле} имеет {значение});
                а также лимит - количество выводимых строк на экран (по умолчанию лимит равен 10)
            </div>
        </div>
        <div class="input-wrapper">
            <div class="group">
                <select name="search_select_column">
                    <option value="*">Все колонки</option>
                    @isset($unchecked_words_columns)
                        @foreach($unchecked_words_columns as $col)
                            @if (!in_array($col, ['id', 'word_id', 'locale'])) 
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endif
                        @endforeach
                    @endisset
                </select>
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">что выбираем</p>
            </div>
            <div class="group">
                <select name="search_select_where_key">
                    <option value="">поле</option>
                    @isset($unchecked_words_columns)
                        @foreach($unchecked_words_columns as $col)
                            <option value="{{ $col }}">{{ $col }}</option>
                         @endforeach
                    @endisset
                </select>
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">условие: где ...</p>
            </div>
            <div class="group">
                <input type="search" name="search_select_where_value" placeholder="значение" >
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">равно</p>
            </div>
            <div class="group">
                <input type="number" name="search_select_limit" placeholder="10" style="max-width: 100px;">
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">Лимит</p>
            </div>
        </div>
        <button value="unchecked_words"  name='table_name' onclick="selectFromDB(this)">Выбрать</button>

    </div>
    <div id="table-wrapper" style="margin-top: 0;">
        @isset($selected)
            @if($table_name === 'unchecked_words')
                @include('table')
            @endif
        @endisset
    </div>

    <div class="admin-row">
        <label class="section-title">Изменение записей</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Чтобы изменить значение в записях базы данных,
                необходимо выбрать колонку, в которой значение будет изменено,
                а также условие, по которому будет найдена данная строка (key - value),
                где key - навзание колонки, а value - её текущее значение.
            </div>
        </div>
        <div class="input-wrapper">
            <div class="group">
                <input type="search" name="search_update" placeholder="Введите новое значение">
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">на что изменяем</p>
            </div>
            <div class="group">
                <select name="search_update_where_key">
                    <option value="">...</option>
                    @isset($unchecked_words_columns)
                        @foreach($unchecked_words_columns as $col)
                            @if ($col !== 'locale')
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endif
                        @endforeach
                    @endisset
                </select>
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">условие: где...</p>
            </div>
            <div class="group">
                <input type="search" name="search_update_where_value" placeholder="значение" >
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">равно значению</p>
            </div>
        </div>
        <button onclick="updateDB(this)">Изменить</button>
    </div>

    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(session('success'))
        <div style="color: green; background: #e6fffa; padding: 10px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif
    <div id="js-message-container" style="display: none; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
           <span id="js-message-text"></span>
       </div>
</div>
</body>
<footer>
    &copy; 2026 AppL10N Service
</footer>
</html>
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
    function selectFromDB(element){
        let parent = element.closest('.admin-row');
        const data = {
            search_select_column: parent.querySelector('[name="search_select_column"]').value,
            search_select_where_key: parent.querySelector('[name="search_select_where_key"]').value,
            search_select_where_value: parent.querySelector('[name="search_select_where_value"]').value,
            search_select_limit: parent.querySelector('[name="search_select_limit"]').value,
            table_name: parent.querySelector('[name="table_name"]').value,
        };

        let form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('select_tr.file') }}";

        let token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '{{ csrf_token() }}';
        form.appendChild(token);

        for (let key in data) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
    function updateDB(element){
        let row = element.closest('.admin-row');
        let selects = row.querySelectorAll("select");
        let inputs = row.querySelectorAll("input");
        const data = {
            search_update: document.getElementsByName('search_update')[0].value,
            search_update_where_key: document.getElementsByName('search_update_where_key')[0].value,
            search_update_where_value: document.getElementsByName('search_update_where_value')[0].value,
        };
        alterDB("{{ route("update_tr.file") }}", data);
        inputs.forEach(input => {
            input.value = '';
        });
        selects.forEach(select => {
            select.selectedIndex = 0;
        });
    }
    function alterDB(routeName, inputName, element){
        let dataToSend = {};
        if (typeof inputName === 'string'){
            let input = document.getElementsByName(inputName)[0];
            dataToSend[inputName] = input ? input.value : '';
        }else {
            dataToSend = inputName;
        }
        fetch(routeName, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dataToSend)
        })
            .then(response => {return response.ok ? response.json() : response.text();})
            .then(data => {console.log(data); showStatus('Готово! Данные успешно сохранены.', false, element); })

            .catch(error => {
                console.error('Ошибка:', error);
                showStatus('Ошибка: ' . error.message, true, element);
            });
    }
    function showStatus(message, isError = false, eventElement) {
        const container = document.getElementById('js-message-container');
        const textSpan = document.getElementById('js-message-text');

        if (eventElement){
            let closestRow = eventElement.closest('.admin-row');
            if (closestRow){
                closestRow.after(container);
            }
        }

        container.style.display = 'block';
        container.style.backgroundColor = isError ? '#fee2e2' : '#dcfce7';
        container.style.color = isError ? 'var(--error)' : 'var(--success)';
        container.style.border = `1px solid ${isError ? 'var(--error)' : 'var(--success)'}`;

        textSpan.innerText = message;

        // через 5 секунд сообщение скрывается
        setTimeout(() => {
            container.style.display = 'none';
        }, 5000);
    }
    function switchPage(button){
        let url = button.getAttribute('data-url');
        if (!url) return;

        const container = document.getElementById('ajax-table-container');

        button.innerHTML = 'Загрузка';
        button.disabled = true;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.text())
            .then(html => {
                if (container) {
                    container.outerHTML = html;

                    // Прокрутка к началу таблицы для удобства
                    const scrollBox = document.querySelector('.table-scroll-container');
                    if (scrollBox) scrollBox.scrollTop = 0;
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                button.disabled = false;
                button.innerText = 'Ошибка. Повторить?';
            });
    }
</script>

