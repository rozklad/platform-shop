@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ $product->product_title }} ::
@parent
@stop

{{-- Page description --}}
@section('meta-description')
@if ( $product->product_description )
{{ strip_tags($product->product_description) }}
@else
{{ $product->product_title }}
@endif
@stop

{{-- Queue Assets --}}
{{ Asset::queue('ilightbox', 'sanatorium/shop::ilightbox/src/css/ilightbox.css') }}
{{ Asset::queue('ilightbox', 'sanatorium/shop::ilightbox/src/js/ilightbox.js', 'jquery') }}

{{-- Partial Assets --}}
@section('assets')
@parent
@stop

{{-- Meta general --}}
@section('meta-general')
	{{-- OG tags --}}
	<meta property="og:title" content="{{ $product->product_title }}" />
	<meta property="og:description" content="{{ strip_tags($product->product_description) }}" />
	<meta property="og:type" content="product" />
	<meta property="og:url" content="{{ $product->url }}" />
	@if ( $product->hasCoverImage() )
		<meta property="og:image" content="{{ $product->coverThumb('full') }}" />
	@endif

	{{-- Cannonical --}}
	<link rel="canonical" href="{{ $product->url }}" />

@stop


{{-- Inline Styles --}}
@section('styles')
@parent
<style type="text/css">
.tab-panel .tab-content {
	padding: 20px 0;
}
.tab-panel .nav-pills {
	margin-top: 20px;
}
.tab-panel .nav-pills li a {
	border-radius: 0;
}
.tab-panel .nav-pills li.active a,
.tab-panel .nav-pills li a:hover {
	background-color: #333;
	color: #f2f2f2;
}
.tab-panel .nav-pills li a {
	background-color: #666;
	color: #ccc;
}
.tab-panel .product-description {
	font-size: 16px;
	line-height: 1.75em;
}
.image-area, .product-info {
	min-height: 500px;
}

.primary-image {
	text-align: center;
	height: 500px;
}

.primary-image:before {
	content: "";
	height: 100%;
	vertical-align: middle;
	display: inline-block;
}

.primary-image a {
	display: inline-block;
    vertical-align: middle;
    max-width: 99%;
}

.primary-image a img {
	max-height: 500px;
    max-width: 100%;
}
.social-share {
	list-style-type: none;
	padding-left: 0;
}
.social-share li {
	display: inline-block;
}
.social-share li a {
	padding: 8px;
}
</style>
@stop

{{-- Inline Scripts --}}
@section('scripts')
@parent
<script>
jQuery(document).ready(function($) {

	// iLightbox
	$('.lightbox').bind('click', function(event) {
		
		event.preventDefault();

		var defaults = {
				startFrom: 0
			},
			settings = $(this).data(),	// Load inline configuration
			config = $.extend(defaults, settings);

		for ( var key in config ) {
			switch( key ) {
				case 'startfrom':
					config['startFrom'] = config[key];
				break;
			}
		}

		$.iLightBox([
			@if ( is_array($product->getGalleryImages()) )
				@foreach($product->getGalleryImages() as $thumb => $image)
					{ url: '{{ $image }}', title: '{{ $product->product_title }}' },
				@endforeach
			@endif
		], config);
	});

});
</script>
@stop


@section('page')

<div class="show-product" itemscope itemtype="http://schema.org/Product">

	<ol class="breadcrumb">
		<li><a href="{{ URL::to('/') }}">{{ config('platform.app.title') }}</a></li>
		@foreach( $product->categories as $category )
			<li><a href="{{ $category->url }}">{{ $category->category_title }}</a></li>
		@endforeach
	</ol>

	<h1 itemprop="name" class="product-title">{{{ $product->product_title }}}</h1>

	<div class="row">
		<div class="col-sm-6 image-area">
			{{-- Primary image --}}
			<div class="primary-image text-center">
				@if ( $product->hasCoverImage() )
				<a href="{{ $product->coverThumb() }}" class="lightbox" data-title="{{ $product->product_title }} - Ukázkový obrázek" data-gallery="product_images" title="{{ trans('sanatorium/shop::products.click_for_preview', ['title' => $product->product_title]) }}">
					<img src="{{ $product->coverThumb(300) }}" alt="{{ $product->product_title }} - Ukázkový obrázek">
				</a>
				@endif
			</div>

			{{-- Invisible gallery --}}
			<div class="gallery-area">
				@if ( is_array($product->getGalleryImages()) )
					@foreach($product->getGalleryImages() as $thumb => $image)
						<a href="{{ $image }}" class="lightbox" data-title="{{ $product->product_title }}" data-gallery="product_images">
							<img src="{{ $thumb }}" alt="{{ $product->product_title }}">
						</a>
					@endforeach
				@endif
			</div>
		</div>
		<div class="col-sm-6 product-info">
			{{-- todo: make dynamic --}}
			<meta itemprop="url" content="{{ URL::to($product->url) }}">
			<div class="row">
				
			</div>
			<table class="table product-info-table">
				<tbody>
				<tr>
					<th>
						EAN
					</th>
					<td>
						{{ $product->code }}
					</td>
				</tr>
				@if (is_object($product->manufacturer))
				<tr>
					<th>
						Výrobce
					</th>
					<td>
						@foreach( $product->manufacturer as $manufacturer)
						<a href="{{ $manufacturer->url }}" class="manufacturer-title show-product-blue-text">{{ $manufacturer->manufacturer_name }}</a>
						@endforeach
					</td>
				</tr>
				@endif
				<tr>
					<th colspan="2" class="blank">

					</th>
				</tr>
				<tr>
					<th>
						Cena s DPH
					</th>
					<td>
						{{ $product->price_vat }}
					</td>
				</tr>
				<tr>
					<th>
						Cena bez DPH
					</th>
					<td>
						{{ $product->price }}
					</td>
				</tr>
				<tr>
					<th colspan="2" class="blank">

					</th>
				</tr>
				<tr>
					<th>
						Dostupnost
					</th>
					<td>
						<span class="{{ $product->availability_slug }}">
							{{ $product->availability }}
						</span>
					</td>
				</tr>
				</tbody>
			</table>

			<br />

			{{-- Buy --}}
			<div>
				{{-- Buy form requires $product object --}}
				@hook('catalog.product.bottom', $product)
			</div>
			<br />

			@hook('product.detail.bottom', $product)

		</div>
	</div>

	{{-- Tabs --}}
	<div class="row">
		<div class="col-sm-12">
			<div class="tab-panel">

				<!-- Nav tabs -->
				{{-- TODO: dynamic logic --}}
				<ul class="nav nav-tabs">

					{{-- Product description tab head --}}
					@if ( $product->product_description )
					<li role="presentation" class="active">
						<a href="#description" aria-controls="description" role="tab" data-toggle="tab">
							{{ trans('sanatorium/shop::products/common.tabs.description') }}
						</a>
					</li>
					@endif

					@if ( $product->mediaByTag('attachments')->count() )
					<li role="presentation">
						<a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
							{{ trans('sanatorium/shop::products/common.tabs.attachments') }}
						</a>
					</li>
					@endif

					@if ( $product->product_urls )
					<li role="presentation">
						<a href="#urls" aria-controls="urls" role="tab" data-toggle="tab">
							{{ trans('sanatorium/shop::products/common.tabs.urls') }}
						</a>
					</li>
					@endif

					{{-- Product attributes tab head --}}
					@if ( isset($attributes) )
						@if ( is_object($attributes) )
							@if ( count($attributes->attributesValues) > 0 )
							<li role="presentation" @if ( !$product->product_description ) class="active" @endif>
								<a href="#params" aria-controls="params" role="tab" data-toggle="tab">
									{{ trans('sanatorium/shop::products/common.tabs.params') }}
								</a>
							</li>
							@endif
						@endif
					@endif

				</ul>
				<div class="tab-content show-product-common-text">

					{{-- Product description tab --}}
					@if ( $product->product_description )
					<div role="tabpanel" class="tab-pane product-description active" id="description">
						<p>{!! nl2br($product->product_description) !!}</p>
					</div>
					@endif

					{{-- Attachments tab --}}
					@if ( $product->mediaByTag('attachments')->count() )
					<div role="tabpanel" class="tab-pane product-description" id="attachments">
						@attributes($product, ['attachments'])
					</div>
					@endif

					{{-- Urls --}}
					@if ( $product->product_urls )
					<div role="tabpanel" class="tab-pane product-description" id="urls">
						@attributes($product, ['product_urls'])
					</div>
					@endif

					{{-- Product attributes tab --}}
					@if ( isset($attributes) )
						@if ( is_object($attributes) )
							@if ( count($attributes->attributesValues) > 0 )
							<div role="tabpanel" class="tab-pane @if ( !$product->product_description ) active @endif" id="params">
								<ul class="list-unstyled">
									@foreach ($attributes->attributesValues as $attribute)
									<li><strong class="param-label">{{ $attribute->name }}</strong> <span class="param-value">{{{ $attribute->value }}}</span></li>
									@endforeach
									{{-- @each('sanatorium/shop::products/partials/parameters', $attributes->subGroups, 'child') --}}
								</ul>
							</div>
							@endif
						@endif
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

@stop
