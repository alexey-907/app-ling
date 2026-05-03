@foreach($selected as $row)
    <tr>
        @foreach((array)$row as $value)
            <td style="padding: 10px; border: 1.5px solid var(--border);">{{ $value }}</td>
        @endforeach
    </tr>
@endforeach
