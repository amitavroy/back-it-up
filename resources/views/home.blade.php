@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3>List of backups</h3></div>

                    <div class="panel-body">
                        A list of the backups taken for the Database and a link to download them.
                    </div>

                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Created on</th>
                            <th>Sql file size</th>
                            <th>Dump time</th>
                            <th>Zip file size</th>
                            <th>Zip time</th>
                            <th>Link</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>{{$record->created_at}}</td>
                                <td>{{$record->sql_file_size}}</td>
                                <td>{{$record->dump_time}}</td>
                                <td>{{$record->gzip_file_size}}</td>
                                <td>{{$record->compress_time}}</td>
                                <td><a href="{{$record->url}}" target="_blank">Download</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{$records->render()}}
                </div>
            </div>
        </div>
    </div>
@endsection
