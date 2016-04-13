@extends('layouts/default')

{{-- Page title --}}
@section('title')
@parent
{{{ trans("action.{$mode}") }}} {{ trans('sanatorium/shop::products/common.title') }}
@stop

{{-- Queue assets --}}
{{ Asset::queue('validate', 'platform/js/validate.js', 'jquery') }}

{{ Asset::queue('selectize', 'selectize/css/selectize.bootstrap3.css', 'styles') }}
{{ Asset::queue('selectize', 'selectize/js/selectize.js', 'jquery') }}

{{ Asset::queue('dropzone', 'sanatorium/inputs::dropzone/dropzone.js') }}
{{ Asset::queue('dropzone', 'sanatorium/inputs::dropzone/dropzone.css') }}

{{-- Inline scripts --}}
@section('scripts')
@parent
<script type="text/javascript">
	Dropzone.options.attributeDropzoneGallery = {
		url: '{{ route('sanatorium.inputs.dropzone.upload') }}',
		previewTemplate: document.querySelector('#template-container-media').innerHTML,
		previewsContainer: ".dropzone-previews-gallery",
		uploadMultiple: true,
		parallelUploads: 100,
		maxFiles: 100,
		sending: function(file, xhr, formData) {
			formData.append("_token", '{{ csrf_token() }}');
			formData.append("entity_type", '{{ str_replace('\\', '(BACKSLASH)', get_class($product)) }}');
			formData.append("entity_id", '{{ $product->id }}');
			formData.append("attribute_slug", 'gallery');
		},
		success: function(file, response) {
			activateFiles();
		}
	};

	function activateFiles() {
		$('#attribute-dropzone-gallery .dz-preview')
		.css({
			'cursor': 'pointer'
		})
		.unbind('click')
		.on('click', function(event)
		{
			event.preventDefault();

			var id = $(this).data('id'),
			$self = $(this);

			$.ajax({
				url: '{{ route('sanatorium.inputs.dropzone.cover') }}',
				type: 'POST',
				data: {id: id, product_id: {{ $product->id }} }
			}).success(function(){
				$('.cover-image').removeClass('cover-image');
				$self.addClass('cover-image');
			});

		});
	}

	$(function(){

		activateFiles();

		$('#attribute-dropzone-gallery button.btn-delete')
		.on('click', function(e)
		{
			e.preventDefault();
			var that = $(this).parents('.dz-image');
			$.ajax({
				url: '{{ route('sanatorium.inputs.dropzone.delete') }}',
				type: 'delete',
				data: {
					media_id : $(this).data('medium-id')
				},
				dataType: 'json',
				success : function(data)
				{
					if(data.status)
					{
						that.remove();
					}
				},
				error : function(a,b,c)
				{
					console.log(a,b,c);
				}

			});
		});
	});

	$(function(){
		$('#tags').selectize({
			create: true, sortField: 'text',
		});
	});

	{{-- @todo: dunno why this is not feeded from the included view --}}
	$(function(){
		var $statusText = $('.media-select-uploaded-file-after_image');

		$('#media-select-upload-after_image').change(function(event){

			var formdata = new FormData(),
				file = document.getElementById("media-select-upload-after_image").files[0];
			formdata.append("file", file);
			formdata.append("entity_type", "{{ str_replace('\\', '(BACKSLASH)', get_class($product)) }}");
			formdata.append("entity_id", {{ $product->id }});
			formdata.append("attribute_slug", "after_image");

			$.ajax({
				url 				: "{{ route('sanatorium.inputs.dropzone.upload.avatar') }}",
				type 				: "POST",
				cache 				: false,
				processData 		: false, 
	            contentType 		: false,
				data 				: formdata,
				enctype 			: 'multipart/form-data'
	        }).done(function( data ) {
	        	
	          	// Indicate loading finished
	          	$statusText.html('<span class="text-success">{{ trans('common.success') }}</span>');
	          	$('.media-select-upload-progress-after_image').addClass('invisible');
	          	
	          	$('#preview-after_image').attr('src', data.thumbnail);
	        });

	        // Indicate loading
	        $('.media-select-upload-progress-after_image').removeClass('invisible');
	        $statusText.html(file.name);

	        return false;

		});

		$('#media-select-upload-before_image').change(function(event){

			var formdata = new FormData(),
				file = document.getElementById("media-select-upload-before_image").files[0];
			formdata.append("file", file);
			formdata.append("entity_type", "{{ str_replace('\\', '(BACKSLASH)', get_class($product)) }}");
			formdata.append("entity_id", {{ $product->id }});
			formdata.append("attribute_slug", "before_image");

			$.ajax({
				url 				: "{{ route('sanatorium.inputs.dropzone.upload.avatar') }}",
				type 				: "POST",
				cache 				: false,
				processData 		: false, 
	            contentType 		: false,
				data 				: formdata,
				enctype 			: 'multipart/form-data'
	        }).done(function( data ) {
	        	
	          	// Indicate loading finished
	          	$statusText.html('<span class="text-success">{{ trans('common.success') }}</span>');
	          	$('.media-select-upload-progress-before_image').addClass('invisible');
	          	
	          	$('#preview-before_image').attr('src', data.thumbnail);
	        });

	        // Indicate loading
	        $('.media-select-upload-progress-before_image').removeClass('invisible');
	        $statusText.html(file.name);

	        return false;

		});
	});
</script>
@stop

{{-- Inline styles --}}
@section('styles')
@parent
<style type="text/css">
	.attributes-inline hr, .attributes-inline .btn-primary,
	.attributes-inline legend {
		display: none;
	}
</style>
@stop

{{-- Page content --}}
@section('page')

<section class="panel panel-default panel-tabs">

	{{-- Form --}}
	<form id="shop-form" action="{{ request()->fullUrl() }}" role="form" method="post" data-parsley-validate>

		{{-- Form: CSRF Token --}}
		<input type="hidden" name="_token" value="{{ csrf_token() }}">

		<header class="panel-heading">

			<nav class="navbar navbar-default navbar-actions">

				<div class="container-fluid">

					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#actions">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>

						<a class="btn btn-navbar-cancel navbar-btn pull-left tip" href="{{ route('admin.sanatorium.shop.products.all') }}" data-toggle="tooltip" data-original-title="{{{ trans('action.cancel') }}}">
							<i class="fa fa-reply"></i> <span class="visible-xs-inline">{{{ trans('action.cancel') }}}</span>
						</a>

						<span class="navbar-brand">{{{ trans("action.{$mode}") }}} <small>{{{ $product->exists ? $product->id : null }}}</small></span>
					</div>

					{{-- Form: Actions --}}
					<div class="collapse navbar-collapse" id="actions">

						<ul class="nav navbar-nav navbar-right">

							@if ($product->exists)
							<li>
								<a href="{{ route('admin.sanatorium.shop.products.delete', $product->id) }}" class="tip" data-action-delete data-toggle="tooltip" data-original-title="{{{ trans('action.delete') }}}" type="delete">
									<i class="fa fa-trash-o"></i> <span class="visible-xs-inline">{{{ trans('action.delete') }}}</span>
								</a>
							</li>
							@endif

							<li>
								<button class="btn btn-primary navbar-btn" data-toggle="tooltip" data-original-title="{{{ trans('action.save') }}}">
									<i class="fa fa-save"></i> <span class="visible-xs-inline">{{{ trans('action.save') }}}</span>
								</button>
							</li>

						</ul>

					</div>

				</div>

			</nav>

		</header>

		<div class="panel-body">

			<div role="tabpanel">

				{{-- Form: Tabs --}}
				<ul class="nav nav-tabs" role="tablist">
					<li class="active" role="presentation"><a href="#general-tab" aria-controls="general-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.general') }}}</a></li>
					{{--
					<li role="presentation"><a href="#attributes-tab" aria-controls="attributes-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.attributes') }}}</a></li>--}}
					
					<li role="presentation"><a href="#pricing-tab" aria-controls="pricing-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.pricing') }}}</a></li>
					<li role="presentation"><a href="#tags-tab" aria-controls="tags-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.tags') }}}</a></li>
					<li role="presentation"><a href="#urls-tab" aria-controls="urls-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.urls') }}}</a></li>
					<li role="presentation"><a href="#attachments-tab" aria-controls="attachments-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.attachments') }}}</a></li>
					<li role="presentation"><a href="#categories-tab" aria-controls="categories-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.categories') }}}</a></li>
					<li role="presentation"><a href="#manufacturers-tab" aria-controls="manufacturers-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.manufacturers') }}}</a></li>
					<li role="presentation"><a href="#before_after-tab" aria-controls="before_after-tab" role="tab" data-toggle="tab">Before / After</a></li>
				</ul>

				<div class="tab-content">

					{{-- Tab: General --}}
					<div role="tabpanel" class="tab-pane fade in active" id="general-tab">

						<fieldset>

							<div class="row">

								<div class="col-sm-6">
									
									<div class="form-group{{ Alert::onForm('gallery', ' has-error') }}">
										<label for="attribute-dropzone-gallery" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/shop::products/model.general.gallery_help') }}}"></i>
											{{{ trans('sanatorium/shop::products/model.general.gallery') }}}
										</label>
									</div>

									<div class="dropzone dropzone-previews-gallery" id="attribute-dropzone-gallery" style="padding-bottom:300px;">
										{{-- Dropzone preview item --}}
										<div id="template-container-media" class="hidden">
											<div class="dz-preview dz-file-preview">
												<div class="dz-details">
													<div class="dz-filename"><span data-dz-name></span></div>
													<div class="dz-size" data-dz-size></div>
													<img data-dz-thumbnail />
												</div>
												<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
												<div class="dz-success-mark"><span>✔</span></div>
												<div class="dz-error-mark"><span>✘</span></div>
												<div class="dz-error-message"><span data-dz-errormessage></span></div>
											</div>
										</div>

										{{-- Default Dropzone message --}}
										<div class="dz-message dz-default" data-dz-message>
											<div class="upload__instructions">
												<div class="dnd"></div>

												<i class="fa fa-upload fa-5x"></i>
												<h4>{{ trans('sanatorium/inputs::types.file.select') }}</h4>
												<p class="lead">{{ trans('sanatorium/inputs::types.file.allowed') }}</p>
												<p class="small">
													<i>
														{{ implode(', ', config('cartalyst.filesystem.allowed_mimes')) }}
													</i>
												</p>

											</div>
										</div>

										{{-- Already uploaded file --}}
										@foreach( $product->mediaByTag('gallery') as $medium )
										<div class="dz-preview dz-file-preview @if ( in_array('cover', $medium->tags()->lists('name')->toArray() ) )
											cover-image
											@endif" data-id="{{ $medium->id }}" data-cover="Náhledový obrázek">
											<div class="dz-details">
												<div class="dz-filename">{{ $medium->name }}</div>
												<div class="dz-size">{{ formatBytes($medium->size) }}</div>
												@if ( $medium->is_image )
												<img src="{{ route('thumb.view', $medium->path) . '?w=250&h=250' }}" class="img-preview" alt="~" title="{{{ $product->product_title }}}" style="max-width:100%;height:auto;">
												@else
												@if ( ($medium->mime == 'audio/ogg') || ($medium->mime == 'video/mp4') || ($medium->mime == 'video/ogg') )

												<i class="fa fa-file-movie-o fa-5x"></i>

												@elseif ( $medium->mime == 'application/zip')

												<i class="fa fa-file-zip-o fa-5x"></i>

												@elseif ( $medium->mime == 'application/pdf')

												<i class="fa fa-file-pdf-o fa-5x"></i>

												@else

												<i class="fa fa-file-o fa-5x"></i>

												@endif
												@endif
											</div>
											<div class="dz-success-mark"><span>✔</span></div>
											<div class="dz-error-mark"><span>✘</span></div>
											<div class="dz-error-message"><span data-dz-errormessage></span></div>
											<button class="btn btn-link btn-delete pull-right" data-medium-id="{{{ $medium->id }}}">
												<i class="fa fa-times"></i>
											</button>
										</div>
										@endforeach
										{{-- Fallback --}}
										@if ( count( $product->mediaByTag('cover') ) == 0 )
										@if ( $product->cover_image )
										<?php $medium = $product->cover_image; ?>
										<div class="dz-preview dz-file-preview cover-image" data-id="{{ $medium->id }}" data-cover="Náhledový obrázek">
											<div class="dz-details">
												<div class="dz-filename">{{ $medium->name }}</div>
												<div class="dz-size">{{ formatBytes($medium->size) }}</div>
												@if ( $medium->is_image )
												<img src="{{ route('thumb.view', $medium->path) . '?w=250&h=250' }}" class="img-preview" alt="~" title="{{{ $product->product_title }}}" style="max-width:100%;height:auto;">
												@else
												@if ( ($medium->mime == 'audio/ogg') || ($medium->mime == 'video/mp4') || ($medium->mime == 'video/ogg') )

												<i class="fa fa-file-movie-o fa-5x"></i>

												@elseif ( $medium->mime == 'application/zip')

												<i class="fa fa-file-zip-o fa-5x"></i>

												@elseif ( $medium->mime == 'application/pdf')

												<i class="fa fa-file-pdf-o fa-5x"></i>

												@else

												<i class="fa fa-file-o fa-5x"></i>

												@endif
												@endif
											</div>
											<div class="dz-success-mark"><span>✔</span></div>
											<div class="dz-error-mark"><span>✘</span></div>
											<div class="dz-error-message"><span data-dz-errormessage></span></div>
											<button class="btn btn-link btn-delete pull-right" data-medium-id="{{{ $medium->id }}}">
												<i class="fa fa-times"></i>
											</button>
										</div>
										@endif
										@endif
									</div>


								</div>

								<div class="col-sm-6">

									<div class="attributes-inline">

										@attributes($product, ['product_title'])

									</div>

									<div class="attributes-inline">

										@attributes($product, ['product_description'])

									</div>

									<div class="attributes-inline">

										@attributes($product, ['video'])

									</div>

									<div class="form-group{{ Alert::onForm('slug', ' has-error') }}">

										<label for="slug" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/shop::products/model.general.slug_help') }}}"></i>
											{{{ trans('sanatorium/shop::products/model.general.slug') }}}
										</label>

										<input type="text" class="form-control" name="slug" id="slug" placeholder="{{{ trans('sanatorium/shop::products/model.general.slug') }}}" value="{{{ input()->old('slug', $product->slug) }}}">

										<span class="help-block">{{{ Alert::onForm('slug') }}}</span>

									</div>

									<div class="form-group{{ Alert::onForm('code', ' has-error') }}">

										<label for="code" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/shop::products/model.general.code_help') }}}"></i>
											{{{ trans('sanatorium/shop::products/model.general.code') }}}
										</label>

										<input type="text" class="form-control" name="code" id="code" placeholder="{{{ trans('sanatorium/shop::products/model.general.code') }}}" value="{{{ input()->old('code', $product->code) }}}">

										<span class="help-block">{{{ Alert::onForm('code') }}}</span>

									</div>

									<div class="form-group{{ Alert::onForm('ean', ' has-error') }}">

										<label for="ean" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/shop::products/model.general.ean_help') }}}"></i>
											{{{ trans('sanatorium/shop::products/model.general.ean') }}}
										</label>

										<input type="text" class="form-control" name="ean" id="ean" placeholder="{{{ trans('sanatorium/shop::products/model.general.ean') }}}" value="{{{ input()->old('ean', $product->ean) }}}">

										<span class="help-block">{{{ Alert::onForm('ean') }}}</span>

									</div>

									<div class="form-group{{ Alert::onForm('weight', ' has-error') }}">

										<label for="weight" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/shop::products/model.general.weight_help') }}}"></i>
											{{{ trans('sanatorium/shop::products/model.general.weight') }}}
										</label>

										<input type="text" class="form-control" name="weight" id="weight" placeholder="{{{ trans('sanatorium/shop::products/model.general.weight') }}}" value="{{{ input()->old('weight', $product->weight) }}}">

										<span class="help-block">{{{ Alert::onForm('weight') }}}</span>

									</div>

									<div class="form-group{{ Alert::onForm('stock', ' has-error') }}">

										<label for="stock" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/shop::products/model.general.stock_help') }}}"></i>
											{{{ trans('sanatorium/shop::products/model.general.stock') }}}
										</label>

										<input type="text" class="form-control" name="stock" id="stock" placeholder="{{{ trans('sanatorium/shop::products/model.general.stock') }}}" value="{{{ input()->old('stock', $product->stock) }}}">

										<span class="help-block">{{{ Alert::onForm('stock') }}}</span>

									</div>

								</div>

							</div>

						</fieldset>

					</div>

					{{-- Tab: Attributes --}}
					{{-- 
					<div role="tabpanel" class="tab-pane fade" id="attributes-tab">
						@attributes($product)
					</div>
					--}}

					{{-- Tab: Pricing --}}
					<div role="tabpanel" class="tab-pane fade" id="pricing-tab">
						@pricing($product)
					</div>

					{{-- Tab: Tags --}}
					<div role="tabpanel" class="tab-pane fade" id="tags-tab">						

						<fieldset>

							<legend>{{{ trans('platform/tags::model.tag.legend') }}}</legend>

							@tags($product, 'tags')

						</fieldset>

					</div>
					
					<?php /*
					<div class="tab-pane fade" id="urls-tab">
						
						<div class="attributes-inline">

							@attributes($product, ['product_urls'])

						</div>

					</div>*/?>

					<div class="tab-pane fade" id="attachments-tab">
						
						<div class="attributes-inline">

							@attributes($product, ['attachments'])

						</div>

					</div>

					<div class="tab-pane fade" id="categories-tab">
						
						<div class="attributes-inline">

							@categories($product, 0, 'default-tree')

						</div>

					</div>

					<div class="tab-pane fade" id="manufacturers-tab">
						
						<div class="attributes-inline">

							@manufacturers($product)

						</div>

					</div>

					<div class="tab-pane fade" id="before_after-tab">
						
						<div class="attributes-inline">

							@attributes($product, ['before_image', 'after_image'])

						</div>

					</div>

				</div>

			</div>

		</div>

	</form>

</section>
@stop
