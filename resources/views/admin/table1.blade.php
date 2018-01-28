<html>
<head></head>
<body>
<h1>总数：{{ count($res) }}个</h1>
<table border="3">
    <tr>
        <td>name</td>
        <td>passwd</td>
        <td>info</td>
        <td>mark</td>
        <td>time</td>
    </tr>

    @if(count($res))
        @foreach($res as $vo)
    <tr>
        <td>{{ $vo -> name }}</td>
        <td>{{ $vo -> passwd }}</td>
        <td>{{ $vo -> info }}</td>
        <td>{{ $vo -> mark }}</td>
        <td>{{ date('Y-m-d H:i',$vo -> time) }}</td>
    </tr>
        @endforeach
    @endif
</table>
</body>
</html>