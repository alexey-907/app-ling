<div class="result-box" id="ajax-table-container" style="width: 100%; box-sizing: border-box;">
    @if($selected->isNotEmpty())
        <label class="section-title" style="color: #6eb9ff; padding-left:30px; padding-top: 10px; margin: 1px; display: block;">Результат выборки:</label>
        <div class="card result-box" style="margin-top: 20px; background: #fff; padding: 5px 20px 15px 25px;">
            <div class="table-scroll-container" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem table-layout: auto;">
                    <thead>
                    <tr style="background: #f8fafc; text-align: left">
                        @foreach(array_keys((array)$selected->first()) as $column)
                            <th style="padding: 10px; border: 2.5px solid var(--border); text-align: center">{{ $column }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody id="table-body">
                    @include('table-rows', ['selected' => $selected])
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-container" style="margin-top: 20px; display: flex; justify-content: center;">
            @if(!$selected->onFirstPage())
                <button class="prev-page-btn"
                        data-url="{{ $selected->previousPageUrl() }}"
                        onclick="switchPage(this)"
                        style="min-width: 140px; padding: 15px; margin: 10px; background: #2d86c5; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    ← Предыдущие {{$limit > 100 ? 100 : $limit}}
                </button>
            @endif

            {{-- Кнопка появится, если лимит > 100 И есть данные на следующей странице --}}
            @if($selected->hasMorePages())
                <button id="next-page-btn"
                        data-url="{{ $selected->nextPageUrl() }}"
                        onclick="switchPage(this)"
                        style="min-width: 140px; padding: 15px; margin: 10px; cursor: pointer;">
                    Следующие {{$limit > 100 ? 100 : $limit}} →
                </button>
            @endif
        </div>
    @else
        <p style="color: var(--error); padding-bottom: 50px; text-align: center; font-size: 1.5rem;">К СОЖАЛЕНИЮ, НИЧЕГО НЕ НАЙДЕНО</p>
    @endif
</div>
