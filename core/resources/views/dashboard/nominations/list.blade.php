@extends('dashboard.layouts.master')
@section('title', 'Nominations')
@section('content')
    <div class="padding">
        <div class="box">
            <div class="box-header dker">
                <h3>Nominations</h3>
                <small>
                    <a href="{{ route('adminHome') }}">{{ __('backend.home') }}</a> /
                    <a href="">Nominations</a>
                </small>
            </div>
            @if($Nominations->total() == 0)
                <div class="row p-a">
                    <div class="col-sm-12">
                        <div class=" p-a text-center ">
                            {{ __('backend.noData') }}
                        </div>
                    </div>
                </div>
            @endif

            @if($Nominations->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered m-a-0">
                        <thead class="dker">
                        <tr>
                            <th class="width20">#</th>
                            <th>Company</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Category</th>
                            <th class="text-center width100">Date</th>
                            <th class="text-center" style="width:100px;">{{ __('backend.options') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($Nominations as $Nomination)
                            <tr>
                                <td class="dker">{{ $Nomination->id }}</td>
                                <td>{{ $Nomination->company }}</td>
                                <td>{{ $Nomination->contact }}@if($Nomination->jobtitle) <small class="text-muted">({{ $Nomination->jobtitle }})</small>@endif</td>
                                <td>{{ $Nomination->email }}</td>
                                <td>{{ $Nomination->phone }}</td>
                                <td>{{ $Nomination->country }}</td>
                                <td>{{ $Nomination->category }}@if($Nomination->subcategory) <small class="text-muted">/ {{ $Nomination->subcategory }}</small>@endif</td>
                                <td class="text-center">{{ $Nomination->created_at ? $Nomination->created_at->format('Y-m-d') : '' }}</td>
                                <td class="text-center">
                                    @if(@Auth::user()->permissionsGroup->delete_status)
                                        <button class="btn btn-sm danger" data-toggle="modal"
                                                data-target="#delete-{{ $Nomination->id }}" title="{{ __('backend.delete') }}">
                                            <small><i class="material-icons">&#xe872;</i></small>
                                        </button>
                                        <div id="delete-{{ $Nomination->id }}" class="modal fade" data-backdrop="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('backend.confirmation') }}</h5>
                                                    </div>
                                                    <div class="modal-body text-center p-lg">
                                                        <p>{{ __('backend.confirmationDeleteMsg') }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn dark-white p-x-md"
                                                                data-dismiss="modal">{{ __('backend.no') }}</button>
                                                        <a href="{{ route('nominationsDestroy', $Nomination->id) }}"
                                                           class="btn danger p-x-md">{{ __('backend.yes') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <footer class="dker p-a">
                    <div class="row">
                        <div class="col-sm-3 hidden-xs">
                            <small class="text-muted inline m-t-sm m-b-sm">{{ __('backend.showing') }} {{ $Nominations->firstItem() }}-{{ $Nominations->lastItem() }} {{ __('backend.of') }}
                                <strong>{{ $Nominations->total() }}</strong></small>
                        </div>
                        <div class="col-sm-9 text-right text-center-xs">
                            {!! $Nominations->links() !!}
                        </div>
                    </div>
                </footer>
            @endif
        </div>
    </div>
@endsection
