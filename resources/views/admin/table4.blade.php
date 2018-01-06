<html>
<head></head>
<body>
<table border="3">
    <tr>
        <td>name</td>
        <td>pwe</td>
        <td>wheree</td>
        <td>beizhu1</td>
        <td>beizhu2</td>
        <td>beizhu3</td>
        <td>beizhu4</td>
    </tr>
    @if(count($res))
        @foreach($res as $vo)
    <tr>
        <td>{{ $vo -> name }}</td>
        <td>{{ $vo -> pwe }}</td>
        <td>{{ $vo -> wheree }}</td>
        <td>{{ $vo -> beizhu1 }}</td>
        <td>{{ $vo -> beizhu2 }}</td>
        <td>{{ $vo -> beizhu3 }}</td>
        <td>{{ $vo -> beizhu4 }}</td>
    </tr>
        @endforeach
    @endif
</table>
</body>
</html>