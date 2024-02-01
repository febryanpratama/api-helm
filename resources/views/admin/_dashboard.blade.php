<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="text-align: center">@lang('label.admin_panel')</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="{{ route('company:report.task_attendance', \Str::slug(auth()->user()->company->Name)) }}">
                            <div class="card">
                                <div class="card-header" style="text-align: center">@lang('label.report')</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('company:user.data', \Str::slug(auth()->user()->company->Name)) }}">
                            <div class="card">
                                <div class="card-header" style="text-align: center">@lang('label.data_user')</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('company:profile.company_edit', [\Str::slug(auth()->user()->company->Name), 'company' => $company->ID]) }}">
                            <div class="card">
                                <div class="card-header" style="text-align: center">
                                    {{ $company->Name }}
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>