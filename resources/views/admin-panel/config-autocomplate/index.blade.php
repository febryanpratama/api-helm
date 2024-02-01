@extends('layouts.master')

@push('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.2/css/bootstrap-multiselect.css" integrity="sha512-tlP4yGOtHdxdeW9/VptIsVMLtgnObNNr07KlHzK4B5zVUuzJ+9KrF86B/a7PJnzxEggPAMzoV/eOipZd8wWpag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.2/css/bootstrap-multiselect.min.css" integrity="sha512-fZNmykQ6RlCyzGl9he+ScLrlU0LWeaR6MO/Kq9lelfXOw54O63gizFMSD5fVgZvU1YfDIc6mxom5n60qJ1nCrQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
	.thead-color {
		background-color: #f6f9fc !important;
	}

	.cards-background {
		background-color: white;
		padding: 20px;
		box-shadow: rgb(149 157 165 / 20%) 0px 8px 24px;
	}
</style>
@endpush

@section('content')
<div class="container mb-4">
	<div class="cards-background mb-3">
		<div class="row">
			<div class="col-12">
				<div class="form-group">
					<label for="">Daftar Kategori</label>
					<br>
					<select name="category_id" id="category-id" class="form-control select-picker" multiple data-live-search="true">
						@foreach($category as $val)
						<option value="{{ $val->id }}">{{ $val->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
		</div>	

		<div class="row">
			<div class="col-6">
				<label for="">Cek Kategori</label>
				<select name="" id="category-id-selected" class="form-control">
					<option value="">--- Pilih Kategori ---</option>
					@foreach($category as $val)
					<option value="{{ $val->id }}">{{ $val->name }}</option>
					@endforeach
				</select>
			</div>
		</div>
	</div>

	<div class="card card-custom">
		<div class="card-header bg-white">
			<div class="float-right">
				<button class="btn btn-sm btn-info text-white" id="reload-data">Reload Data</button>
			</div>

			<b>Setting Autocomplate - Kategori</b>
		</div>

		<div class="card-body">
			<div id="results-autocomplate"></div>
		</div>

		<div class="card-footer" style="display: none;">
			<button class="btn btn-sm btn-primary" id="btn-save">Simpan</button>
		</div>
	</div>
</div>
@stop

@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.2/js/bootstrap-multiselect.min.js" integrity="sha512-lxQ4VnKKW7foGFV6L9zlSe+6QppP9B2t+tMMaV4s4iqAv4iHIyXED7O+fke1VeLNaRdoVkVt8Hw/jmZ+XocsXQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
	$(document).ready(function () {
		$('#category-id').multiselect();

		listAutocomplate()
	})

	$(document).on('change', '#category-id-selected', function () {
		listAutocomplate(this.value, 'show')

		$('.card-footer').css('display', 'none')
	})

	function listAutocomplate(categoryId = '', shows = '') {
		$.ajax({
		    url: `${baseUrl}/admin-panel/config-autocomplete/autocompletes?categoryId=${categoryId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let templates  = `<div class="row" id="autocompletes-data">`
		    	const selected = data.data.selected

		    	if ((data.data.autocomplates).length > 0) {
		    		$.each(data.data.autocomplates, function(key, val) {
		    		 	let select 	= ''
		    		 	let dis 	= ''
		    			
		    			if (selected.includes(val.id)) {
		    				select = 'checked'
		    			}

		    			if (shows) {
		    				dis = 'disabled'
		    			}

		    			templates += `<div class="col-sm-4">
		    				<div class="form-group">
		    					<input type="checkbox" name="autocompletes" id="autocompletes-${val.id}" value="${val.id}" ${select} ${dis}> 
		    						<span class="${(select) ? 'text-success' : ''}">
		    							${(val.prefix) ? (val.prefix + ' - ') : ''} ${val.name}
		    						</span>
		    				</div>
		    			</div>`
		    		})
		    	}

		    	templates += `</div>`

		    	$('#results-autocomplate').html(templates)
				$('#reload-data').attr('disabled', false)

		    	if (!shows) {
			    	$('.card-footer').css('display', '')
		    	}
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	$(document).on('click', '#btn-save', function (e) {
		e.preventDefault()

		// Initialize
		let categoryId 		= $('#category-id').val()
		let autocompleteId 	= new Array()

		$("#autocompletes-data input[type=checkbox]:checked").each(function () {
            autocompleteId.push(this.value)
        })

		// Disabled Button True
    	$(`#btn-save`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/config-autocomplete/store`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	categoryId,
		    	autocompleteId
		    },
		    success: data => {
		    	// Disabled Button False
		    	$(`#btn-save`).attr('disabled', false)

		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    $('#results-autocomplate').html(``)
		    	$('.card-footer').css('display', 'none')
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#btn-save`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	$(document).on('click', '#reload-data', function (e) {
		$(this).attr('disabled', true)
    	$('.card-footer').css('display', '')

		listAutocomplate()
	})
</script>
@endpush