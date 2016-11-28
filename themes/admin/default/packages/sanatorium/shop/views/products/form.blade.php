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
{{ Asset::queue('vue', 'sanatorium/shop::vue/vue.min.js') }}

{{-- Inline scripts --}}
@section('scripts')
@parent
	<script type="text/javascript">
		@if ( $product->exists )
		// Variants table
		var Variants = new Vue({
			el: '#table-variants',
			data: {
				attributes: {!! app('platform.attributes')->whereNamespace(\Sanatorium\Variants\Models\Variant::getEntityNamespace())->lists('name', 'slug')->toJson() !!},
				variants: {!! json_encode(Sanatorium\Variants\Controllers\Admin\VariantsController::getVariants($product)) !!}
			},
			methods: {
				addVariant: function() {
					this.variants.splice(this.variants.length, 0, {
						draft: true
					});
				},
				removeVariant: function(variant) {
					this.variants.$remove(variant);
					this.storeSettings();
				},
				saveVariant: function(variant) {

					if ( typeof variant.attributes == 'undefined' )
					{
						alert('Musíte specifikovat alespoň jednu hodnotu varianty');
						return;
					}

					this.storeSettings();
				},
				storeSettings: function() {
					var settings = JSON.stringify(this.variants);

					var self = this;

					$.ajax({
						url: '{{ route('admin.sanatorium.variants.variants.product') }}',
						type: 'POST',
						data: {
							product: {{ $product->id }},
							settings: settings
						}
					}).success(function(data){
						self.variants = data;
					});
				}
			}
		});
		@endif
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

							<li>
								<a href="{{ route('sanatorium.shop.products.view', $product->slug) }}" class="tip" data-toggle="tooltip" data-original-title="{{{ trans('action.show') }}}" target="_blank">
									<i class="fa fa-eye"></i> <span class="visible-xs-inline">{{{ trans('action.show') }}}</span>
								</a>
							</li>

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
					<li role="presentation"><a href="#attributes-tab" aria-controls="attributes-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.attributes') }}}</a></li>
					<li role="presentation"><a href="#pricing-tab" aria-controls="pricing-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.pricing') }}}</a></li>
					<li role="presentation"><a href="#tags-tab" aria-controls="tags-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.tags') }}}</a></li>
					<li role="presentation"><a href="#categories-tab" aria-controls="categories-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.categories') }}}</a></li>
					<li role="presentation"><a href="#manufacturers-tab" aria-controls="manufacturers-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/shop::products/common.tabs.manufacturers') }}}</a></li>
					<li role="presentation"><a href="#variants-tab" aria-controls="variants-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/variants::variants/common.title') }}}</a></li>
				</ul>

				<div class="tab-content">

					{{-- Tab: General --}}
					<div role="tabpanel" class="tab-pane fade in active" id="general-tab">

						<fieldset>

							<div class="row">

								<div class="col-sm-6">

									<div class="attributes-inline">

										@attributes($product, ['product_cover'])

									</div>

									<div class="attributes-inline">

										@attributes($product, ['product_gallery'])

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
					<div class="tab-pane fade" id="attributes-tab">

						<div class="attributes-inline">

							@attributesnot($product, ['product_cover',
							'product_gallery',
							'product_title',
							'product_description',
							'video'])

						</div>

					</div>

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

					{{-- Tab: Categories --}}
					<div class="tab-pane fade" id="categories-tab">
						
						<div class="attributes-inline">

							@categories($product, 0, 'default-tree')

						</div>

					</div>

					{{-- Manufacturers --}}
					<div class="tab-pane fade" id="manufacturers-tab">
						
						<div class="attributes-inline">

							@manufacturers($product)

						</div>

					</div>

					{{-- Variants --}}
					<div class="tab-pane fade" id="variants-tab">

						@if ( $product->exists )
						<table class="table table-responsive table-variants" id="table-variants">
							<thead>
								<tr>
									<th width="50">#</th>
									<th v-for="attribute in attributes">
										<?= '{{ attribute }}' ?>
									</th>
									<th width="50"></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="variant in variants">
									<td v-if="variant.draft"></td>
									<td v-if="variant.draft" v-for="(slug, attribute) in attributes" style="vertical-align: middle">
										<input type="text" class="form-control" v-model="variant.attributes[slug]">
									</td>
									<td v-if="variant.draft" style="vertical-align: middle">
										<a @click="saveVariant(variant)" class="btn btn-primary">
											<i class="fa fa-save"></i>
										</a>
									</td>

									<td v-if="!variant.draft" style="vertical-align: middle"><small><?= '{{ variant.id }} ' ?></small></td>
									<td v-if="!variant.draft" v-for="(slug, attribute) in attributes" style="vertical-align: middle">
										<?= '{{ typeof variant.attributes[slug] != "undefined" ? variant.attributes[slug] : "—" }}' ?>
									</td>
									<td v-if="!variant.draft" style="vertical-align: middle">
										<a @click="removeVariant(variant)" class="btn btn-default">
											<i class="fa fa-trash-o"></i>
										</a>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="<?= '{{ attributes.length + 1 }} ' ?>">
										<a @click="addVariant()" class="btn btn-default">
											<i class="fa fa-plus"></i>
										</a>
									</td>
								</tr>
							</tfoot>
						</table>
						@else
							<p class="alert alert-info">
								Produkt musíte nejprve uložit, aby bylo možné specifikovat varianty
							</p>
						@endif

					</div>

				</div>

			</div>

		</div>

	</form>

</section>
@stop
