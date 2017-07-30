Hi,

<p>The database backup has been taken.</p>

<table border="1" cellpadding="2" cellspacing="2">
    <thead>
    <tr>
        <td>Data</td>
        <td>Current</td>
        <td>Last</td>
    </tr>
    </thead>
    <tr>
        <td>Count</td>
        <td>{{$data->id}}</td>
        <td>{{$lastRecord->id}}</td>
    </tr>
    <tr>
        <td><strong>DB File size</strong></td>
        <td>{{$data->sql_file_size}}</td>
        <td>{{$lastRecord->sql_file_size}}</td>
    </tr>
    <tr>
        <td><strong>Dump time</strong></td>
        <td>{{$data->dump_time}}</td>
        <td>{{$lastRecord->dump_time}}</td>
    </tr>
    <tr>
        <td><strong>GZip File size</strong></td>
        <td>{{$data->gzip_file_size}}</td>
        <td>{{$lastRecord->gzip_file_size}}</td>
    </tr>
    <tr>
        <td><strong>Compression time</strong></td>
        <td>{{$data->compress_time}}</td>
        <td>{{$lastRecord->compress_time}}</td>
    </tr>
</table>

<p>The file can be found on this url: <a href="{{$data->url}}">Download</a></p>