<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="">
                <h4>
                    <a href="#" data-toggle="collapse" data-target="#collapseManageUser">1. @lang('label.manage_users')</a>
                </h4>
            </div>
            <div id="accordionManageUser">
                <div id="collapseManageUser" class="collapse" aria-labelledby="addProject" data-parent="#accordionManageUser">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>&bull; <a href="{{route('company:user.form', \Str::slug(auth()->user()->company->Name))}}" style="color:black;"> <strong> @lang('label.input_new_user')</strong></a> @lang('label.easily')</h5>
                                <h5>&bull; @lang('label.view_and') <a href="{{route('company:user.data', \Str::slug(auth()->user()->company->Name))}}" style="color:black;"> <strong>@lang('label.manage_users')</strong></a> @lang('label.easily')</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="row" style="margin-top:5px;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="">
                <h4>
                    <a href="#" data-toggle="collapse" data-target="#collapseManageProject"> 2. @lang('label.manage_project_task_knowledge')</a>
                </h4>
            </div>
            <div id="accordionManageProject">
                <div id="collapseManageProject" class="collapse" aria-labelledby="addProject" data-parent="#accordionManageProject">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>
                                    &bull; 
                                    <a href="#" id="new_project" data-toggle="collapse" data-target="#collapseAddProject" style="color:black;"> <strong>@lang('label.input_new_projects'),</strong></a> 
                                    <a href="#" id="new_task" data-toggle="collapse" data-target="#collapseAddTask" style="color:black;"> <strong>@lang('label.new_tasks'),</strong></a>
                                    @lang('label.or')
                                    <a href="#" id="new_knowledge" data-toggle="collapse" data-target="#collapseAddKnowledge" style="color:black;"> <strong>*@lang('label.new_knowledge')</strong></a>
                                    @lang('label.easily')
                                </h5>
                                <h5>
                                    &bull; @lang('label.view_and')
                                    <a href="#" id="list_project" data-toggle="collapse" data-target="#collapseMyProject" style="color:black;"> <strong>@lang('label.manage_projects'),</strong></a>
                                    <a href="#" id="list_task" data-toggle="collapse" data-target="#collapseOne" style="color:black;"> <strong>@lang('label.manage_tasks'),</strong></a>
                                    <a href="#" id="list_knowledge" data-toggle="collapse" data-target="#collapseMyKnowledge" style="color:black;"> <strong>*@lang('label.manage_knowledge')</strong></a>
                                    @lang('label.easily')
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<div class="row" style="margin-top:5px;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="">
                <h4>
                    <a href="#" data-toggle="collapse" data-target="#collapseManageGroup">3. @lang('label.create_group_chat_to_communicate_with_colleagues_and_partners')</a>
                </h4>
            </div>
            <div id="accordionManageGroup">
                <div id="collapseManageGroup" class="collapse" aria-labelledby="addProject" data-parent="#accordionManageGroup">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>&bull; <a href="#" id="new_group" data-toggle="collapse" data-target="#collapseAddConversation" style="color:black;"> <strong>@lang('label.input_new_group_chat')</strong></a>
                                    @lang('label.communicate_with_colleagues_and_partners')
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>