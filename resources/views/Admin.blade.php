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

    <div class="admin-row">
        <label class="section-title">Вставка новых строк</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Для вставки новых слов необходимо загрузить файл,
                в поддерживаемом формате (смотрите список форматов),
                который содержит строки с ключом и строки со значением,
                далее выбрать, в какое поле должны быть вставлены строки файла,
                и нажать на кнопку "Вставить строки".
            </div>
        </div>
        <div class="input-wrapper">
            <form action="{{ route('insert.file') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="lang" value="en">
                <select name="lang" id="lang" style="max-width: max-content; text-align: left">
                    <option value="">where</option>
                    @isset($locales)
                        @foreach($locales as $row)
                                <option value="{{ $row->locale }}">{{ $row->locale }} - {{ $row->language}}</option>
                        @endforeach
                    @endisset
                </select>
                <div class="allowed_extensions">
                    <div class="file-upload-group">
                        <input name="filename" type="file">
                    </div>
                    <div class="text_allowed_extensions">
                        <div onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
                            <p  style="font-size: 0.76rem; font-style:italic">Список поддерживаемых форматов</p>
                            <div class="info">
                                .json, .ini, .csv, .po
                            </div>
                        </div>
                    </div>
                </div>
                <button value="Вставить строки">Вставить строки</button>
            </form>
        </div>
    </div>

    <div class="admin-row">
        <label class="section-title">Ручная вставка</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Ручная вставка позволяет собственноручно вписать значения для конкретных полей (колонок).
                После нажатия кнопки "Вставить" в таблицу на НОВЫЕ строки будут всавлены введенные данные.
                Те поля, значения которых не было указано, станут пустыми (в таблице равно типу "null")
            </div>
        </div>
        <div class="unfold-group">
        <p class="unfold" onclick="unfold(this)">Развернуть ▼</p>
            <div class="input-wrapper_list" style="display: none; flex-direction: column">
                @isset($columns)
                    @foreach($columns as $col)
                        @if (!in_array($col, ['id', 'value', 'original_id', 'check_required'])) 
                            <div class="field_manual_insert" style="display: flex">
                                <input type="search" name="search_manual_insert" placeholder="Значение поля">
                                <label class="field_name">{{ $col }}</label>
                            </div>
                        @endif
                    @endforeach
                @endisset
            </div>
        </div>
        <button value="Ручная вставка" type="button" onclick="manualInsert(this)">Вставить</button>
    </div>

    <div class="admin-row">
        <label class="section-title">Выбор значений</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Чтобы выбрать строки из таблицы нужно указать условие:
                поле (колонку) и её значение, по которому будет(-ут) выбрана(-ы) строка(-и).
                Либо можно выбрать "Все колонки", тогда будут выведено то количество строк из таблицы,
                которое указано в поле "Лимит" (по умолчанию лимит равен 10)
            </div>
        </div>
        <div class="input-wrapper">
            <!-- <div class="group">
                    <select name="search_select_column">
                    <option value="*">Все колонки</option>
                    @isset($columns)
                        @foreach($columns as $col)
                            <option value="{{ $col }}">{{ $col }}</option>
                        @endforeach
                    @endisset
                </select>
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">что выбираем</p>
            </div> -->
            <div class="group">
            <select name="search_select_where_key">
                <option value="">поле</option>
                    <option value="translated_words.id">id</option>
                     @isset($columns)
                        @foreach($columns as $col)
                            @if (!in_array($col, ['original_id', 'id'])) 
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endif
                        @endforeach
                    @endisset

                <!-- @isset($columns)
                    @foreach($columns as $col)
                        <option value="{{ $col }}">{{ $col }}</option>
                    @endforeach
                @endisset -->
            </select>
            <p style="text-align: center; font-size: 0.76rem; font-style:italic">условие: где ...</p>
            </div>
            <div class="group">
            <input type="search" name="search_select_where_value" value="{{ session('old_input.search_select_where_value') }}" placeholder="значение" style="min-width: 500px;" >
            <p style="text-align: center; font-size: 0.76rem; font-style:italic">равно</p>
            </div>
            <div class="group">
                <input type="search" name="search_select_limit" value="{{ session('old_input.search_select_limit') }}" placeholder="10" style="max-width: 100px;">
            <p style="text-align: center; font-size: 0.76rem; font-style:italic">Лимит</p>
            </div>
        </div>
        
        <button value="Выбрать"  onclick="selectFromDB(this)">Выбрать</button>
    </div>
    <div id="table-wrapper" style="margin-top: 0;">
        @isset($selected)
        <br>
        @include('table')
        @endisset
    </div>

    <div class="admin-row">
        <label class="section-title">Изменение значений</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Чтобы изменить значение в строке таблицы,
                необходимо выбрать колонку, в которой значение будет изменено,
                а также условие, по которому будет найдена данная строка (key - value),
                где key - навзание колонки, а value - её текущее значение.
            </div>
        </div>
        <div class="input-wrapper">
            <div class="group">
            <select name="search_update_column">
                <option value="">...</option>
                @isset($columns)
                    @foreach($columns as $col)
                        @if ($col == 'meaning' || $col == 'check_required')
                            <option value="{{ $col }}">{{ $col }}</option>
                        @endif
                    @endforeach
                @endisset
            </select>
            <p style="text-align: center; font-size: 0.76rem; font-style:italic">что изменяем</p>
            </div>
            <div class="group">
            <input type="search" name="search_update" placeholder="Введите новое значение">
            <p style="text-align: center; font-size: 0.76rem; font-style:italic">на что изменяем</p>
            </div>
            <div class="group">
            <select name="search_update_where_key">
                <option value="">...</option>
                @isset($columns)
                    <option value="translated_words.id">id</option>
                    @foreach($columns as $col)
                        @if ($col != 'id' && $col != 'original_id')
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




    <div class="admin-row">
        <label class="section-title">Добавить язык</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Чтобы добавить новый язык,
                нужно сначала указать существующую локаль для этого языка (обычно 2 буквы английского алфавита, например, pl) 
                а затем ввести полное название языка, допустимо вводить на языке добавляемой локали (например, Польский или Polski).
            </div>
        </div>
        <div class="input-wrapper">
            <div class="group">
                <input type="search" name="search_add_locale" placeholder="Введите название">
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">название локали</p>
            </div>
            <div class="group">
                <input type="search" name="search_add_language" placeholder="Введите название">
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">название языка</p>
            </div>
        </div>
        <button value="Добавить" onclick="addLocale(this)">Добавить</button>
    </div>

    <div class="admin-row">
        <label class="section-title">Удалить язык</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info">
                Чтобы удалить язык,нужно указать его локаль и нажать кнопку "Удалить". <br>
                ВНИМАНИЕ! Удаление языка приведёт к удалению всех данных, которые с ним связаны без возможности их восстановления! <br>
            </div>
        </div>
        <div class="input-wrapper">
            <div class="group">
                <select name="search_delete_locale">
                    <option value="">Выберите поле</option>
                        @isset($locales)
                            @foreach($locales as $row)
                                    <option value="{{ $row->locale }}">{{ $row->locale}}</option>
                            @endforeach
                        @endisset
                </select>
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">название поля для удаления</p>
            </div>
        </div>
        <button value="Удалить" onclick="showWarning(this)">Удалить</button>
        <div class="warning">
            <p>ВНИМАНИЕ! <br> Данные будут удалены безвозвратно. <br> Вы уверены?<br></p>
            <div class="btn-group">
                <button name="cancel" onclick="cancelDeleting(this)" style="background: #64748b;">Отмена</button>
                <button name="delete_anyway" onclick="deleteLocale(this)" style="background: var(--error);">Удалить</button>
            </div>
        </div>
    </div>


    <div class="admin-row">
        <label class="section-title">Удалить запись</label>
        <div class="img" onmouseover="showInfo(this)" onmouseout="hideInfo(this)" style="cursor: pointer;">
            <img src="{{ asset('images/question.png') }}" alt="Информация">
            <div class="info" style="max-width: 50rem">
                Чтобы удалить запись (строку) из таблицы, нужно выбрать поле и его значение,
                которое содержится в данной записи, затем нажать на кнопку "Удалить" (справа от поля). <br>
                ВНИМАНИЕ! При удалении строки удалятся данные из каждой ячейки этой строки!
                Если хотите удалить/изменить значение конкретной ячейки, выбирайте пункт "Изменение строк". <br>
            </div>
        </div>
        <div class="input-wrapper">
            <div class="group">
                <select name="search_delete_column">
                    <option value="">Выберите поле</option>
                    <option value="translated_words.id">id</option>
                     @isset($columns)
                        @foreach($columns as $col)
                            @if (!in_array($col, ['check_required', 'locale', 'original_id', 'id'])) 
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endif
                        @endforeach
                    @endisset
                </select>
            <p style="text-align: center; font-size: 0.76rem; font-style:italic">удалить запись, где значение поля ...</p>
            </div>

            <div class="group">
                <input type="search" name="search_delete" placeholder="значение" >
                <p style="text-align: center; font-size: 0.76rem; font-style:italic">равно ...</p>
            </div>
        </div>
        <button value="Удалить" onclick="showWarning(this)">Удалить</button>

        <div class="warning">
            <p>ВНИМАНИЕ! <br> Данные будут удалены безвозвратно. <br> Вы уверены?<br></p>
            <div class="btn-group">
                <button name="cancel" onclick="cancelDeleting(this)" style="background: #64748b;">Отмена</button>
                <button name="delete_anyway" onclick="deleteFromDB(this)" style="background: var(--error);">Удалить</button>
            </div>
        </div>
    </div>

    <div id="js-message-container" style="display: none; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
        <span id="js-message-text"></span>
    </div>

    @if(session('success'))
        <div style="color: green; background: #e6fffa; padding: 10px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

</div>
</body>
<footer>
    &copy; 2026 AppL10N Service
</footer>


</html>
<script>
    function showWarning(element){
        let parent = element.closest('.admin-row');
        let select = parent.querySelector('select');
        let input = parent.querySelector('input');

        if (input && input.value.trim() === '' ){
            showStatus('Введите данные для удаления', true, element);
            return; 
        }
        if (select.value === '') {
            showStatus('Введите данные для удаления', true, element);
            return; 
        }

        let row = element.closest('.admin-row');
        let warning = row.querySelector('.warning');
    

        if (warning.classList.contains('warning')){
            warning.style.display = 'flex';
        } else{
            warning.style.display = 'none';
        }
    }
    function cancelDeleting(element){
        let row = element.closest('.admin-row');
        let warning = row.querySelector('.warning');
        if (warning.classList.contains('warning')){
            warning.style.display = 'none';
        }
        select.value = '';
    }
    function showInfo(element) {
        let parent = element.closest('.admin-row');
        let select = parent.querySelector('select');
        let input = parent.querySelector('input');

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

    function unfold(element){
        let input_wrapper = element.nextElementSibling;

        const isVsiible = input_wrapper.style.display === 'flex';
        if (isVsiible) {
            input_wrapper.style.display = 'none';
            element.textContent = 'Развернуть ▼';
        } else {
            input_wrapper.style.display = 'flex';
            element.textContent = 'Свернуть ▲';
        }
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

    function manualInsert(element){
        let field_names = [];
        let inserted = [];

        let hasEmpty = false;
        
        let all_field_names = document.querySelectorAll('.field_name');
        all_field_names.forEach((label) => {
            let field_name = label.textContent.trim();
            field_names.push(field_name);
        });
        
        
        let input_inserted = document.getElementsByName('search_manual_insert');
        input_inserted.forEach((input) => {
            let field_name = input.value;
            if (field_name == '') hasEmpty = true;
            inserted.push(field_name);
        });
        
        if (hasEmpty) {
            showStatus('Введите все данные для вставки', true, element);
            return; 
        }
        
        let dataToSave = [];

        for (let i = 0; i < field_names.length; i++) {
            dataToSave.push({
                field_name: field_names[i],
                value: inserted[i] || ''
            });
        }
        let url = "{{ route("manualInsert.file") }}";
        alterDB(url, {lines: dataToSave}, element /*массив json, где lines - название переменной ключа */);
    }

    function selectFromDB(element){
        const data = {
            search_select_where_key: document.getElementsByName('search_select_where_key')[0].value,
            search_select_where_value: document.getElementsByName('search_select_where_value')[0].value,
            search_select_limit: document.getElementsByName('search_select_limit')[0].value,
        };

        let table = document.getElementById('table-wrapper');
        if (element){
            table.style.display = 'block';
        }
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('select.file') }}";

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
    function updateDB(element){

        let parent = element.closest('.admin-row');
        let select = parent.querySelectorAll('select');
        let input = parent.querySelectorAll('input');

        if (select[0].value === '' || select[1].value === '' || input.value === '') {
            showStatus('Введите все данные для изменения', true, element);
            return; 
        }

        let row = element.closest('.admin-row');
        let selects = row.querySelectorAll("select");
        let inputs = row.querySelectorAll("input");

        const data = {
            search_update_column: document.getElementsByName('search_update_column')[0].value,
            search_update: document.getElementsByName('search_update')[0].value,
            search_update_where_key: document.getElementsByName('search_update_where_key')[0].value,
            search_update_where_value: document.getElementsByName('search_update_where_value')[0].value,
        };

        alterDB("{{ route("update.file") }}", data, element);

        inputs.forEach(input => {
            input.value = '';
        });

        selects.forEach(select => {
            select.selectedIndex = 0;
        });
    }
    function addLocale(element){
        let nameLocale = document.getElementsByName('search_add_locale')[0];
        let nameLang = document.getElementsByName('search_add_language')[0];

        if (!nameLang.value || !nameLocale.value){
            showStatus('Введите название поля', true, element);
            return
        }
        const data = {
            search_add_locale: nameLocale.value,
            new_language: nameLang.value,
        };
        alterDB("{{ route("addLocale.file") }}", data, element);
    }
    function deleteLocale(element){
        let row = element.closest('.admin-row');
        let select = row.querySelector("select");

        if (select.value === ''){
            showStatus('Введите название поля', true, element);
            return
        }
        alterDB("{{ route("deleteLocale.file") }}", "search_delete_locale", element);
        select.value = '';
        cancelDeleting(element);
    }
    function deleteFromDB(element){
        let row = element.closest('.admin-row');
        let select = row.querySelector("select[name='search_delete_column']");
        let input = row.querySelector("input[name='search_delete']");
        const data = {
            search_delete_column: document.getElementsByName('search_delete_column')[0].value,
            search_delete: document.getElementsByName('search_delete')[0].value
    };
        alterDB("{{ route("delete.file") }}", data, element);
        select.value = '';
        input.value = '';
        cancelDeleting(element);
    }
</script>

