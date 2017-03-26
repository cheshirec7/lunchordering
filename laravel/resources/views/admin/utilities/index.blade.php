@extends('layouts.app')
@section('title','Utilities')
@section('styles')
    <style>
        body{background-color:#fafafa;}
    </style>
@endsection
@section('content')
    <div id="spinner"></div>

    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Utilities</h3>
                <hr/>
                @include('partials.notify')
                <div class="text-sm-center">
                    <div>
                        <a href="/admin/utilities/updateallcreditsdebits" class="btn btn-primary waves-effect">Update
                            All Credits and Debits</a>
                    </div>
                    <br/>
                    <div>
                        <a class="disabled" href="#">Delete All Lunches for Date / Teacher / Grade</a>
                    </div>
                    <br/>
                    <div>
                        <a class="disabled" href="#">Reset System</a>
                    </div>
                    <br/>
                </div>
                </ul>
            </div>
        </div>
    </div>
@endsection


