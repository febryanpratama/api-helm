<div class="modal fade" id="company-modal" tabindex="-1" role="dialog" aria-labelledby="company-modal-aria" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="company-modal-aria"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" enctype="multipart/form-data">
			  @csrf
				<div class="modal-body">
			    <div class="row">
			    	<div class="col-md-6 col-12">
			        <div class="form-group">
		        	    <label for="" class="">@lang('label.name')</label> <span class="text-danger">*</span>
		        			
		        			<input id="company-name" type="text" class="form-control" name="name" value="{{ auth()->user()->company->Name }}" readonly="" placeholder="Masukkan Nama Perusahaan">
			        </div>
			    	</div>

			    	<div class="col-md-6 col-12">
		    			<div class="form-group">
		    				<label for="" class="">@lang('label.phone')</label> <span class="text-danger">*</span>

		    				<input id="company-phone" type="number" class="form-control" name="phone" value="{{ auth()->user()->company->Phone }}" placeholder="Masukkan Nomer Hp">
		    			</div>
	    			</div>
			    </div>

			    <div class="row">
			    	<div class="col-md-6 col-12">
			    		<div class="form-group">
			    			<label for="" class="">@lang('label.email')</label> <span class="text-danger">*</span>

			    			<input id="company-email" type="email" class="form-control" name="email" value="{{ (auth()->user()->company->Email == '-') ? '' : auth()->user()->company->Email }}" placeholder="Masukkan Email">
			    		</div>
			    	</div>

			    	<div class="col-md-6 col-12">
			    		<div class="form-group">
			    			<label for="" class="">@lang('label.logo')</label> <span class="text-danger">*</span> <br>

			    			<button class="btn btn-info btn-sm text-white" id="logo-btn-file" type="button"><i class="fa fa-camera"></i></button>
			    			<span id="span-name-file-logo" class="pl-2"><i>*Tidak ada file yang dipilih</i></span>
			    			<input type="file" name="logo" class="form-control hide-element" id="file-logo" accept="image/png,image/jpg,image/jpeg">
			    		</div>
			    	</div>
			    </div>

			    <div class="row">
			    	<div class="col-md-6">
			    		<div class="form-group">
			    			<label for="">Link Facebook (Profil/Halaman/Grup)</label> <span class="text-danger">*</span> <br>
			    			<input type="text" name="facebook" class="form-control" id="facebook-url" placeholder="Ex: https://facebook.com" value="{{ auth()->user()->company->facebook }}">
			    		</div>
			    	</div>

			    	<div class="col-md-6">
			    		<div class="form-group">
			    			<label for="">Link Instagram</label>
			    			<input type="text" name="instagram" class="form-control" placeholder="Ex: https://instagram.com" value="{{ auth()->user()->company->instagram }}">
			    		</div>
			    	</div>
			    </div>

			    <div class="row">
			    	<div class="col-md-6">
			    		<div class="form-group">
			    			<label for="">Link Youtube</label>
			    			<input type="text" name="youtube" class="form-control" placeholder="Ex: https://youtube.com" value="{{ auth()->user()->company->youtube }}">
			    		</div>
			    	</div>

			    	<div class="col-md-6">
			    		<div class="form-group">
			    			<label for="">Link LinkedIn</label>
			    			<input type="text" name="linkedin" class="form-control" placeholder="Ex: https://www.linkedin.com" value="{{ auth()->user()->company->linkedin }}">
			    		</div>
			    	</div>
			    </div>
			    
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
					<button type="submit" class="btn btn-company btn-sm text-white" id="company-btn-loading">Simpan</button>
				</div>
      </form>
    </div>
  </div>
</div>