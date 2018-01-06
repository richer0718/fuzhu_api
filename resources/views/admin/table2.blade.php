<html>
<head></head>
<body>
<table border="3">
    <tr>
        <td>name</td>
        <td>passwd</td>
        <td>info</td>
        <td>info2</td>
        <td>timee</td>
    </tr>
    @if(count($res))
        @foreach($res as $vo)
    <tr>
        <td>{{ $vo -> name }}</td>
        <td>{{ $vo -> passwd }}</td>
        <td>{{ $vo -> info }}</td>
        <td>{{ $vo -> info2 }}</td>
        <td>{{ $vo -> timee }}</td>
    </tr>
        @endforeach
    @endif
</table>
</body>
</html>