{{-- Hidden Element --}}
<input type="hidden" id="checkout-url" value="{{ route('auth.logout_process') }}">

<div class="modal fade" id="checkout-modal" tabindex="-1" role="dialog" aria-labelledby="checkout-modal-aria" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-modal">
      <div class="modal-header bg-company border-modal-header">
        <div class="modal-title" id="checkout-modal-aria">
        	<h4><b>Checkout</b></h4>
        	<span class="span-header-checkin-checkout">Silahkan Lengkapi Form Anda</span>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" enctype="multipart/form-data">
      	@csrf
	      <div class="modal-body">
	      	<div class="form-group">
	      		<div class="row cursor-area" id="report-upload-area">
	      		    <div class="col-md-4 col-4 text-center">
	      		        <img src="{{ asset('img/auth/img-upload-checkout.png') }}" alt="report-upload" class="width-height-img-upload-checkout" id="report-upload">
	      		    </div>

	      		    <div class="col-md-8 col-8">
	      		        <div class="mt-3 text-description-upload">
	      		            <h5><b>Unggah Laporan</b></h5>
	      		            <span>
	      		                Unggah File Laporan, Screenshoot atau lainnya untuk membuktikannya.
	      		            </span>
	      		        </div>
	      		    </div>

			      		{{-- Hidden Element --}}
			      		<input type="file" name="file" style="display: none;" id="report-upload-file">
	      		</div>

	      		<div class="row mt-3 dropzone-area" id="dropzone-area-checkout">
	      			{{-- Hidden Element --}}
	      			<input type="hidden" id="checkout-report-file" name="foto">
	      			
      		    <div class="col-md-4 col-4 text-center">
      		        <img src="{{ asset('img/auth/img-upload.png') }}" alt="img-upload" class="width-height-img-upload-checkout upload-foto">
      		    </div>

      		    <div class="col-md-8 col-8">
      		        <div class="mt-3 text-description-upload">
      		            <h5><b>Unggah Foto</b></h5>
      		            <span>
      		                Unggah Foto Selfie atau Foto lain yang membuktikan anda berada ditempat.
      		            </span>
      		        </div>

      		        <div class="dropzone upload-foto-auth" id="upload-foto-checkout" style="min-height: 10px; display: none;">
      		            <div class="dz-default dz-message">@lang('label.upload_here')</div>
      		        </div>
      		    </div>
	      		</div>

	      		<div class="form-group mt-3 mb-4">
	      			<label for="note-checkout">Catatan</label>
	      			<textarea name="note" id="note-checkout" rows="4" placeholder="Masukkan Catatan Tambahan" class="form-control btn-radius"></textarea>
	      		</div>

	      		<div class="form-group">
	      			<button type="submit" class="btn btn-primary btn-company w-100 btn-radius checkout-button">Checkout</button>
	      		</div>
	      	</div>
	      </div>
      </form>
    </div>
  </div>
</div>