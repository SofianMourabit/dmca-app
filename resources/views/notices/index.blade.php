@extends('app')

@section('content')
    <h1 class="page-heading">Your Notices</h1>

    <table class="table table-striped table-bordered">
        <thead>
        <th>This Content:</th>
        <th>Accesible Here:</th>
        <th>Is Infringing Upon My Work Here:</th>
        <th>Notice Sent:</th>
        <th>Content Removed:</th>
        </thead>
        <tbody>
        @foreach($notices->where('content_removed', 0, false) as $notice)
            <tr>
                <td>{{ $notice->infringing_title }}</td>
                <td>{!! link_to($notice->infringing_link) !!}</td>
                <td>{!! link_to($notice->original_link) !!}</td>
                <td>{{ $notice->created_at->diffForHumans() }}</td>
                <td>
                    <!-- Form Checkbox -->

                       {!! Form::open(['data-remote', 'method' => 'PATCH', 'url' => 'notices/' . $notice->id]) !!}
                        <!-- Form Input -->
                        <div class="form-group">
                            {!! Form::checkbox('content_removed', $notice->content_removed, $notice->content_removed) !!}
                            {!! Form::submit('submit') !!}


                        </div>
                       {!! Form::close() !!}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @unless(count($notices->where('content_removed', 0, false)))
        <p class="bg-info text-center">You haven't sent any DMCA notices yet!</p>
    @endunless


    <h3 class="page-heading">Archived Notices</h3>
    @foreach($notices->where('content_removed', 1, false) as $notice)
        <li>{{ $notice->infringing_title }}</li>
        @endforeach



@endsection