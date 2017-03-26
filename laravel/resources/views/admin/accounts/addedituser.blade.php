<div id="modalUser" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form role="form" id="formUser" autocomplete="off" class="ws-validate">
                <div class="modal-header"></div>
                <div class="modal-body">
                    <div class="msgAlert"></div>

                    <div class="form-group">
                        <label for="accountname_user">Account</label>
                        <input type="text" class="form-control user" id="accountname_user" name="accountname_user"
                               disabled="disabled"/>
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control user" id="first_name" name="first_name" required/>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" class="form-control user" name="last_name" required/>
                    </div>

                    <div class="form-group">
                        <label for="user_type">Type</label>
                        <select id="user_type" name="user_type" class="form-control">
                            <option value="3">Student</option>
                            <option value="4">Teacher</option>
                            <option value="5">Staff</option>
                            <option value="6">Parent</option>
                            <option value="2">Admin</option>
                        </select>
                    </div>

                    <div id="fg_usergrades" class="form-group">
                        <label for="grade_id">Grade</label>
                        <select id="grade_id" name="grade_id" class="form-control">
                            <option value="1">[ Select ]</option>
                            @foreach ($gradelevels as $grade)
                                <option value="{{ $grade->DT_RowId }}">{{ $grade->grade_desc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:none !important">
                        <div id="fg_userteachers" class="form-group">
                            <label for="teacher_id">Teacher</label>
                            <select id="teacher_id" name="teacher_id" class="form-control">
                                <option value="1">[ Select ]</option>
                            </select>
                        </div>
                    </div>
                    <div id="fg_userallowedtoorder" class="form-group">
                        <label for="allowed_to_order">Can Order</label>
                        <select id="allowed_to_order" name="allowed_to_order" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <input type="hidden" name="account_id" value="0"/>
                    <input type="hidden" name="user_id" value="0"/>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="float:left;"
                            tabindex="-1">Cancel
                    </button>
                    <button id="btnSaveUser" type="button" class="btn btn-primary">&nbsp;Save&nbsp;</button>
                </div>
            </form>
        </div>
    </div>
</div>

