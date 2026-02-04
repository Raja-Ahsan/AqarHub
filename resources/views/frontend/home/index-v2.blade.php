@php
    $version = $basicInfo->theme_version;
@endphp
@extends('frontend.layouts.layout-v' . $version)

@section('pageHeading')
    {{ __('Home') }}
@endsection

@section('metaKeywords')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_keyword_home }}
    @endif
@endsection

@section('metaDescription')
    @if (!empty($seoInfo))
        {{ $seoInfo->meta_description_home }}
    @endif
@endsection


@section('content')

    <section class="home-banner home-banner-2">
        <div class="container">
            <div class="swiper home-slider" id="home-slider-1">
                <div class="swiper-wrapper">
                    @foreach ($sliderInfos as $slider)
                        <div class="swiper-slide">
                            <div class="content">
                                <span class="subtitle color-white">{{ $slider->title }}</span>
                                <h1 class="title color-white mb-0">{{ $slider->text }}</h1>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
            <div class="banner-ai-cta d-flex flex-wrap gap-2 justify-content-center mt-4 mb-3" style="opacity: 1; visibility: visible;">
                <button type="button" class="btn btn-dark px-4 py-2" id="banner-show-search-form" style="z-index:2;">{{ __('Start Searching') }}</button>
                <button type="button" class="btn btn-outline-light px-4 py-2" id="banner-open-ai-inline" style="z-index:2;" aria-label="{{ __('Ask Assistant') }}">{{ __('Ask Assistant') }} &rarr;</button>
            </div>
            <div class="banner-filter-form mt-40 d-none" id="banner-filter-form-wrap" data-aos="fade-up">
                <div class="row justify-content-center">
                    <div class="col-xxl-10">
                        <div class="tabs-navigation">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <button class="nav-link btn-md rounded-pill active" data-bs-toggle="tab"
                                        data-bs-target="#rent" type="button">{{ __('Rent') }}</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link btn-md rounded-pill" data-bs-toggle="tab" data-bs-target="#sale"
                                        type="button">{{ __('Sale') }}</button>
                                </li>

                            </ul>
                        </div>
                        <div class="tab-content form-wrapper radius-md">
                            <input type="hidden" id="currency_symbol" value="{{ $basicInfo->base_currency_symbol }}">
                            <input type="hidden" name="min" value="{{ $min }}" id="min">
                            <input type="hidden" name="max" value="{{ $max }}" id="max">

                            <input class="form-control" type="hidden" value="{{ $min }}" id="o_min">
                            <input class="form-control" type="hidden" value="{{ $max }}" id="o_max">
                            <div class="tab-pane fade show active" id="rent">
                                <form action="{{ route('frontend.properties') }}" method="get">
                                    <input type="hidden" name="purposre" value="rent">
                                    <input type="hidden" name="min" value="{{ $min }}" id="min1">
                                    <input type="hidden" name="max" value="{{ $max }}" id="max1">
                                    <div class="grid">
                                        <div class="grid-item">
                                            <div class="form-group">
                                                <label for="search1">{{ __('Location') }}</label>
                                                <input type="text" id="search1" name="location" class="form-control"
                                                    placeholder="{{ __('Location') }}">
                                            </div>
                                        </div>
                                        <div class="grid-item">
                                            <div class="form-group">
                                                <label for="type" class="icon-end">{{ __('Property Type') }}</label>
                                                <select aria-label="#" name="type" class="form-control select2 type"
                                                    id="type">
                                                    <option selected disabled value="">{{ __('Select Property') }}
                                                    </option>
                                                    <option value="all">{{ __('All') }}</option>
                                                    <option value="residential">{{ __('Residential') }}</option>
                                                    <option value="commercial">{{ __('Commercial') }}</option>

                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid-item">
                                            <div class="form-group">
                                                <label for="category" class="icon-end">{{ __('Categories') }}</label>
                                                <select aria-label="#" class="form-control select2 bringCategory"
                                                    id="category" name="category">
                                                    <option selected disabled value="">{{ __('Select Category') }}
                                                    </option>
                                                    <option value="all">{{ __('All') }}</option>
                                                    @foreach ($all_proeprty_categories as $category)
                                                        <option value="{{ @$category->categoryContent->slug }}">
                                                            {{ @$category->categoryContent->name }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid-item city">
                                            <div class="form-group">
                                                <label for="city" class="icon-end">{{ __('City') }}</label>
                                                <select aria-label="#" name="city" class="form-control select2 city_id"
                                                    id="city">
                                                    <option selected disabled value="">{{ __('Select City') }}
                                                    </option>
                                                    <option value="all">{{ __('All') }}</option>
                                                    @foreach ($all_cities as $city)
                                                        <option data-id="{{ $city->id }}"
                                                            value="{{ $city->cityContent?->name }}">
                                                            {{ $city->cityContent?->name }}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid-item">
                                            <label class="price-value">{{ __('Price') }}: <br>
                                                <span data-range-value="filterPriceSliderValue">{{ symbolPrice($min) }}
                                                    -
                                                    {{ symbolPrice($max) }}</span>
                                            </label>
                                            <div data-range-slider="filterPriceSlider"></div>
                                        </div>
                                        <div class="grid-item">
                                            <button type="submit"
                                                class="btn btn-lg btn-primary bg-primary icon-start w-100">
                                                {{ __('Search') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="sale">
                                <form action="{{ route('frontend.properties') }}" method="get">
                                    <input type="hidden" name="purposre" value="sale">
                                    <input type="hidden" name="min" value="{{ $min }}" id="min2">
                                    <input type="hidden" name="max" value="{{ $max }}" id="max2">
                                    <div class="grid">
                                        <div class="grid-item">
                                            <div class="form-group">
                                                <label for="search1">{{ __('Location') }}</label>
                                                <input type="text" id="search1" name="location"
                                                    class="form-control" placeholder="{{ __('Location') }}">
                                            </div>
                                        </div>
                                        <div class="grid-item">
                                            <div class="form-group">
                                                <label for="type1" class="icon-end">{{ __('Property Type') }}</label>
                                                <select aria-label="#" name="type" class="form-control select2 type"
                                                    id="type1">
                                                    <option selected disabled value="">{{ __('Select Property') }}
                                                    </option>
                                                    <option value="all">{{ __('All') }}</option>
                                                    <option value="residential">{{ __('Residential') }}</option>
                                                    <option value="commercial">{{ __('Commercial') }}</option>

                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid-item">
                                            <div class="form-group">
                                                <label for="category1" class="icon-end">{{ __('Categories') }}</label>
                                                <select aria-label="#" class="form-control select2 bringCategory"
                                                    id="category1" name="category">
                                                    <option selected disabled value="">{{ __('Select Category') }}
                                                    </option>
                                                    <option value="all">{{ __('All') }}</option>
                                                    @foreach ($all_proeprty_categories as $category)
                                                        <option value="{{ @$category->categoryContent->slug }}">
                                                            {{ @$category->categoryContent->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid-item city">
                                            <div class="form-group">
                                                <label for="city1" class="icon-end">{{ __('City') }}</label>
                                                <select aria-label="#" name="city"
                                                    class="form-control select2 city_id" id="city1">
                                                    <option selected disabled value="">{{ __('Select City') }}
                                                    </option>
                                                    <option value="all">{{ __('All') }}</option>

                                                    @foreach ($all_cities as $city)
                                                        <option data-id="{{ $city->id }}"
                                                            value="{{ @$city->cityContent->name }}">
                                                            {{ @$city->cityContent->name }}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid-item">
                                            <label class="price-value">{{ __('Price') }}: <br>
                                                <span data-range-value="filterPriceSlider2Value">{{ symbolPrice($min) }}
                                                    -
                                                    {{ symbolPrice($max) }}</span>
                                            </label>
                                            <div data-range-slider="filterPriceSlider2"></div>
                                        </div>
                                        <div class="grid-item">
                                            <button type="submit"
                                                class="btn btn-lg btn-primary bg-primary icon-start w-100">
                                                {{ __('Search') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            @if(config('ai.enabled'))
            <div class="banner-ai-inline-wrap d-none mt-40" id="banner-ai-inline-wrap" data-aos="fade-up">
                <div class="row justify-content-center">
                    <div class="col-12 col-xxl-10 ai-assistant-card-outer" style="z-index:2;">
                        <div class="ai-property-assistant-card" id="ai-assistant-card-movable">
                            <div class="ai-assistant-card-header">
                                <button type="button" class="ai-assistant-location-btn" id="ai-assistant-header-location" title="{{ __('Search properties near my location') }}" aria-label="{{ __('Search properties near my location') }}">
                                    <svg class="ai-location-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <span class="ai-assistant-location-btn-text">{{ __('Near me') }}</span>
                                </button>
                                <div class="ai-assistant-card-header-text">
                                    <h2 class="ai-assistant-card-title">{{ __('AI Property Assistant') }}</h2>
                                    <p class="ai-assistant-card-subtitle">{{ __('Powered by Logic Works AE') }}</p>
                                    <span class="ai-assistant-online"><span class="ai-online-dot"></span> {{ __('Online') }}</span>
                                </div>
                                <button type="button" class="ai-assistant-fullscreen-toggle" id="ai-assistant-fullscreen-toggle" title="{{ __('View in full screen') }}" aria-label="{{ __('View in full screen') }}">
                                    <span class="ai-fullscreen-icon-expand" aria-hidden="true">⛶</span>
                                    <span class="ai-fullscreen-icon-collapse d-none" aria-hidden="true">✕</span>
                                </button>
                                <!-- <div class="ai-assistant-card-pills">
                                    <span class="ai-pill ai-pill-green"><span class="ai-pill-icon">📍</span> {{ __('Live Property Data') }}</span>
                                    <span class="ai-pill ai-pill-blue"><span class="ai-pill-icon">📈</span> {{ __('Investment Analysis') }}</span>
                                </div> -->
                               
                            </div>
                            <div class="ai-assistant-card-chat">
                                <div class="ai-assistant-inline-messages" id="ai-assistant-inline-messages">
                                    <div class="ai-assistant-msg assistant">
                                        <span class="ai-msg-label">{{ __('Assistant') }}</span>
                                        <span class="ai-msg-body">👋 {{ __('Welcome! I\'m your AI property assistant. I can help you discover amazing property investment opportunities in your area. Ready to find your next deal?') }}</span>
                                    </div>
                                </div>
                                <div class="ai-assistant-inline-examples">
                                    <p class="ai-examples-label">{{ __('Try asking:') }}</p>
                                    <div class="ai-examples-chips">
                                        <button type="button" class="ai-example-chip" data-query="{{ __('Find 2-bedroom apartments under $400,000 in Dubai') }}">{{ __('Find 2-bedroom apartments under $400,000 in Dubai') }}</button>
                                        <button type="button" class="ai-example-chip" data-query="{{ __("What's the average price per square meter in Krakow?") }}">{{ __("What's the average price per square meter in Krakow?") }}</button>
                                        <button type="button" class="ai-example-chip" data-query="{{ __('Compare properties in Warsaw city center') }}">{{ __('Compare properties in Warsaw city center') }}</button>
                                        <button type="button" class="ai-example-chip" data-query="{{ __('Calculate mortgage for a $500,000 property with 20% down payment') }}">{{ __('Calculate mortgage for a $500,000 property with 20% down payment') }}</button>
                                        <button type="button" class="ai-example-chip" data-query="{{ __('Show me houses with gardens in suburban areas') }}">{{ __('Show me houses with gardens in suburban areas') }}</button>
                                    </div>
                                </div>
                                <div class="ai-assistant-inline-input-wrap">
                                    <textarea id="ai-assistant-inline-input" class="ai-assistant-inline-input" rows="2" placeholder="{{ __('Type your question...') }}" maxlength="2000"></textarea>
                                    <button type="button" id="ai-assistant-inline-send" class="ai-assistant-inline-send">{{ __('Send') }}</button>
                                </div>
                                <p class="ai-assistant-inline-cta text-center mt-2 mb-0 small">
                                    {{ __('Want to see detailed analysis and contact details?') }}
                                    <a href="{{ route('frontend.properties') }}" class="ai-assistant-cta-link">{{ __('Browse all properties') }}</a>
                                    {{ __('to unlock our full property intelligence platform!') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="ai-assistant-fullscreen-overlay" class="ai-assistant-fullscreen-overlay d-none" aria-hidden="true">
                <div class="ai-assistant-fullscreen-backdrop" id="ai-assistant-fullscreen-backdrop"></div>
                <div class="ai-assistant-fullscreen-inner" id="ai-assistant-fullscreen-inner"></div>
            </div>
            @endif
            
            <div class="swiper-pagination pagination-fraction mt-40" id="home-slider-1-pagination"></div>
        </div>

        <div class="swiper home-img-slider" id="home-img-slider-1">
            <div class="swiper-wrapper">
                @foreach ($sliderInfos as $slider)
                    <div class="swiper-slide">
                        <img class="lazyload bg-img"
                            src=" {{ asset('assets/img/hero/sliders/' . $slider->background_image) }}">
                    </div>
                @endforeach


            </div>
        </div>
    </section>

    @if ($secInfo->category_section_status == 1)
        <section class="category pt-100 pb-70 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-inline mb-40" data-aos="fade-up">
                            <h2 class="title">{{ $catgorySecInfo->title }}</h2>
                            <!-- Slider navigation buttons -->
                            <div class="slider-navigation">
                                <button type="button" title="Slide prev"
                                    class="slider-btn cat-slider-btn-prev rounded-pill">
                                    <i class="fal fa-angle-left"></i>
                                </button>
                                <button type="button" title="Slide next"
                                    class="slider-btn cat-slider-btn-next rounded-pill">
                                    <i class="fal fa-angle-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" data-aos="fade-up">
                        <div class="swiper" id="category-slider-1">
                            <div class="swiper-wrapper">
                                @forelse ($property_categories as $category)
                                    <div class="swiper-slide mb-30 color-1">
                                        <a
                                            href="{{ route('frontend.properties', ['category' => $category->categoryContent?->slug]) }}">
                                            <div class="category-item bg-white radius-md text-center">
                                                <div class="category-icons ">
                                                    <img
                                                        src="{{ asset('assets/img/property-category/' . $category->image) }}">
                                                </div>
                                                <span
                                                    class="category-title d-block mt-3 m-0 color-medium">{{ $category->categoryContent?->name }}</span>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class=" p-3 text-center mb-30">
                                            <h3 class="mb-0"> {{ __('No Categories Found') }}</h3>
                                        </div>
                                    </div>
                                @endforelse

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($secInfo->featured_properties_section_status == 1)
        <section class="featured-product pt-100 pb-70">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-inline mb-40" data-aos="fade-up">
                            <h2 class="title">{{ $featuredSecInfo->title }}</h2>
                            <!-- Slider navigation buttons -->
                            <div class="slider-navigation">
                                <button type="button" title="Slide prev"
                                    class="slider-btn product-slider-btn-prev rounded-pill">
                                    <i class="fal fa-angle-left"></i>
                                </button>
                                <button type="button" title="Slide next"
                                    class="slider-btn product-slider-btn-next rounded-pill">
                                    <i class="fal fa-angle-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" data-aos="fade-up">
                        <div class="swiper product-slider">
                            <div class="swiper-wrapper">
                                @forelse ($featured_properties as $property)
                                    {{-- property component --}}
                                    <div class="swiper-slide">
                                        <x-property :property="$property" />
                                    </div>
                                @empty
                                    <div class=" p-3 text-center mb-30 w-100">
                                        <h3 class="mb-0"> {{ __('No Featured Property Found') }}</h3>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($secInfo->call_to_action_section_status == 1)
        <section class="video-banner with-radius pt-100 pb-70">
            <!-- Background Image -->
            <div class="bg-overlay">
                <img class="lazyload bg-img" src=" {{ asset('assets/img/' . $callToActionSectionImage) }}">
            </div>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="content mb-30" data-aos="fade-up">
                            <span class="subtitle text-white">{{ $callToActionSecInfo->title }}</span>
                            <h2 class="title text-white mb-10">{{ $callToActionSecInfo?->subtitle }}</h2>
                            <p class="text-white m-0 w-75 w-sm-100">{{ $callToActionSecInfo?->text }}</p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        @if (!empty($callToActionSecInfo?->video_url))
                            <div class="d-flex align-items-center justify-content-center h-100 mb-30" data-aos="fade-up">
                                <a href="{{ $callToActionSecInfo->video_url }}" class="video-btn youtube-popup">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($secInfo->property_section_status == 1)
        <section class="popular-product pt-100 pb-70">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-inline mb-40" data-aos="fade-up">
                            <h2 class="title">{{ $propertySecInfo->title }}</h2>
                            <div class="tabs-navigation">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <button class="nav-link active btn-md rounded-pill" data-bs-toggle="tab"
                                            data-bs-target="#forAll" type="button">{{ __('All Properties') }}</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link btn-md rounded-pill" data-bs-toggle="tab"
                                            data-bs-target="#forRent" type="button">{{ __('For Rent') }}</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link btn-md rounded-pill" data-bs-toggle="tab"
                                            data-bs-target="#forSell" type="button">{{ __('For Sale') }}</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="tab-content" data-aos="fade-up">
                            <div class="tab-pane fade show active" id="forAll">
                                <div class="row">
                                    @forelse ($properties as $property)
                                        {{-- property component --}}
                                        <x-property :property="$property" class="col-xxl-3 col-lg-4 col-sm-6" />
                                    @empty
                                        <div class="p-3 text-center mb-30">
                                            <h3 class="mb-0"> {{ __('No Properties Found') }}</h3>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="tab-pane fade" id="forRent">
                                <div class="row">
                                    @forelse ($properties as $property)
                                        @if ($property->purpose == 'rent')
                                            {{-- property component --}}
                                            <x-property :property="$property" class="col-xxl-3 col-lg-4 col-sm-6" />
                                        @endif
                                    @empty
                                        <div class=" p-3 text-center mb-30">
                                            <h3 class="mb-0"> {{ __('No Properties Found') }}</h3>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="tab-pane fade" id="forSell">
                                <div class="row">
                                    @forelse ($properties as $property)
                                        @if ($property->purpose == 'sale')
                                            {{-- property component --}}
                                            <x-property :property="$property" class="col-xxl-3 col-lg-4 col-sm-6" />
                                        @endif
                                    @empty
                                        <div class=" p-3 text-center mb-30">
                                            <h3 class="mb-0"> {{ __('No Properties Found') }}</h3>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($secInfo->work_process_section_status == 1)
        <section class="work-process pt-100 pb-70">
            <!-- Bg image -->
            <img class="lazyload bg-img" src="{{ asset('assets/front/images/2548hg445t5464676.png') }}">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-center mb-40" data-aos="fade-up">
                            <span class="subtitle">{{ $workProcessSecInfo->title }}</span>
                            <h2 class="title">{{ $workProcessSecInfo?->subtitle }}</h2>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row gx-xl-5">
                            @forelse ($processes as $process)
                                <div class="col-xl-3 col-lg-4 col-sm-6" data-aos="fade-up">
                                    <div class="process-item text-center mb-30 color-1">
                                        <div class="process-icon">
                                            <div class="progress-content">
                                                <span class="h2 lh-1">{{ $loop->iteration }}</span>
                                                <i class="{{ $process->icon }}"></i>
                                            </div>
                                            <div class="progressbar-line-inner">
                                                <svg>
                                                    <circle class="progressbar-circle" r="96" cx="100"
                                                        cy="100" stroke-dasharray="500" stroke-dashoffset="180"
                                                        stroke-width="6" fill="none" transform="rotate(-5 100 100)">
                                                    </circle>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="process-content mt-20">
                                            <h3 class="process-title">{{ $process->title }}</h3>
                                            <p class="text m-0">{{ $process->text }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-center mb-30 w-100">
                                    <h3 class="mb-0"> {{ __('No Work Process Found') }}</h3>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($secInfo->pricing_section_status == 1)
        <section class="pricing-area pt-100 pb-70">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title title-center mb-20" data-aos="fade-up">
                            <span class="subtitle">{{ $pricingSecInfo->title }}</span>
                            <h2 class="title">{{ $pricingSecInfo?->subtitle }}</h2>
                            <p class="text mb-0 w-50 w-sm-100 mx-auto">{{ $pricingSecInfo?->description }}</p>
                        </div>
                    </div>

                    <div class="col-12 ">
                        <div class="section-title title-inline mb-40 justify-content-center" data-aos="fade-up">
                            <div class="tabs-navigation ">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <button class="nav-link active btn-md rounded-pill" data-bs-toggle="tab"
                                            data-bs-target="#forAll1" type="button">{{ __('Monthly') }}</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link btn-md rounded-pill" data-bs-toggle="tab"
                                            data-bs-target="#forRent1" type="button">{{ __('Yearly') }}</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link btn-md rounded-pill" data-bs-toggle="tab"
                                            data-bs-target="#forSell1" type="button">{{ __('Lifetime') }}</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="tab-content" data-aos="fade-up">
                            <div class="tab-pane fade show active" id="forAll1">
                                <div class="row justify-content-center">
                                    @forelse ($packages as $package)
                                        @if ($package->term == 'monthly')
                                            <div class="col-md-6 col-lg-4">
                                                <div class="pricing-item mb-30 radius-lg">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon color-primary"><i
                                                                class="{{ $package->icon }}"></i>
                                                        </div>
                                                        <div class="label">
                                                            <h3>{{ $package->title }}</h3>
                                                        </div>
                                                    </div>


                                                    <div class="d-flex align-items-center mt-15">
                                                        <span class="price">{{ symbolPrice($package->price) }}</span>
                                                        <span class="period text-capitalize">/
                                                            {{ __($package->term) }}</span>
                                                    </div>
                                                    <h5>{{ __("What's Included") }}</h5>
                                                    <ul class="item-list list-unstyled p-0 pricing-list">

                                                        @if ($package->number_of_agent >= 1)
                                                            <li><i class="fal fa-check"></i>

                                                                @if ($package->number_of_agent == 999999)
                                                                    {{ __('Unlimited') }} {{ __('Agents') }}
                                                                @elseif ($package->number_of_agent > 1)
                                                                    {{ $package->number_of_agent }} {{ __('Agents') }}
                                                                @else
                                                                    {{ $package->number_of_agent }} {{ __('Agent') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Agent') }} </li>
                                                        @endif

                                                        @if ($package->number_of_property >= 1)
                                                            <li><i class="fal fa-check"></i>


                                                                @if ($package->number_of_property == 999999)
                                                                    {{ __('Unlimited') }} {{ __('Properties') }}
                                                                @elseif ($package->number_of_property > 1)
                                                                    {{ $package->number_of_property }}
                                                                    {{ __('Properties') }}
                                                                @else
                                                                    {{ $package->number_of_property }}
                                                                    {{ __('Property') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Property') }}
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_property_gallery_images >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property_gallery_images == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_property_gallery_images }}
                                                                @endif
                                                                {{ __('Gallery Images') }} ({{ __('Per Property') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Gallery Images') }} ({{ __('Per Property') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_property_adittionl_specifications >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property_adittionl_specifications == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_property_adittionl_specifications }}
                                                                @endif
                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Property') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Property') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_projects >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @elseif ($package->number_of_property > 1)
                                                                    {{ $package->number_of_projects }}
                                                                    {{ __('Projects') }}
                                                                @else
                                                                    {{ $package->number_of_projects }}
                                                                    {{ __('Project') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Project') }}
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_types >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_project_types == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_types }}
                                                                @endif
                                                                {{ __('Project Types') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Project Types') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_gallery_images >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_project_gallery_images == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_gallery_images }}
                                                                @endif
                                                                {{ __('Gallery Images') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Gallery Images') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_additionl_specifications >= 1)
                                                            <li><i class="fal fa-check"></i>

                                                                @if ($package->number_of_project_additionl_specifications == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_additionl_specifications }}
                                                                @endif

                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                    </ul>
                                                    <a href="{{ auth('vendor')->check() ? route('vendor.plan.extend.index') : route('vendor.login') }}"
                                                        class="btn btn-outline btn-lg rounded-pill w-100">
                                                        {{ __('Get Started') }}</a>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="p-3 text-center mb-30 w-100">
                                            <h3 class="mb-0"> {{ __('No Pricing Plan Found') }}</h3>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="tab-pane fade" id="forRent1">
                                <div class="row justify-content-center">
                                    @forelse ($packages as $package)
                                        @if ($package->term == 'yearly')
                                            <div class="col-md-6 col-lg-4">
                                                <div class="pricing-item mb-30 radius-lg">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon color-primary"><i
                                                                class="{{ $package->icon }}"></i>
                                                        </div>
                                                        <div class="label">
                                                            <h3>{{ $package->title }}</h3>
                                                        </div>
                                                    </div>


                                                    <div class="d-flex align-items-center mt-15">
                                                        <span class="price">{{ symbolPrice($package->price) }}</span>
                                                        <span class="period text-capitalize">/
                                                            {{ __($package->term) }}</span>
                                                    </div>
                                                    <h5>{{ __("What's Included") }}</h5>
                                                    <ul class="item-list list-unstyled p-0 pricing-list">

                                                        @if ($package->number_of_agent >= 1)
                                                            <li><i class="fal fa-check"></i>

                                                                @if ($package->number_of_agent == 999999)
                                                                    {{ __('Unlimited') }} {{ __('Agents') }}
                                                                @elseif ($package->number_of_agent > 1)
                                                                    {{ $package->number_of_agent }} {{ __('Agents') }}
                                                                @else
                                                                    {{ $package->number_of_agent }} {{ __('Agent') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Agent') }} </li>
                                                        @endif

                                                        @if ($package->number_of_property >= 1)
                                                            <li><i class="fal fa-check"></i>


                                                                @if ($package->number_of_property == 999999)
                                                                    {{ __('Unlimited') }} {{ __('Properties') }}
                                                                @elseif ($package->number_of_property > 1)
                                                                    {{ $package->number_of_property }}
                                                                    {{ __('Properties') }}
                                                                @else
                                                                    {{ $package->number_of_property }}
                                                                    {{ __('Property') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Property') }}
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_property_gallery_images >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property_gallery_images == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_property_gallery_images }}
                                                                @endif
                                                                {{ __('Gallery Images') }} ({{ __('Per Property') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Gallery Images') }} ({{ __('Per Property') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_property_adittionl_specifications >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property_adittionl_specifications == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_property_adittionl_specifications }}
                                                                @endif
                                                                {{ __('Additional Features') }}({{ __('Per Property') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Property') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_projects >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @elseif ($package->number_of_property > 1)
                                                                    {{ $package->number_of_projects }}
                                                                    {{ __('Projects') }}
                                                                @else
                                                                    {{ $package->number_of_projects }}
                                                                    {{ __('Project') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Project') }}
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_types >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_project_types == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_types }}
                                                                @endif
                                                                {{ __('Project Types') }}({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Project Types') }}({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_gallery_images >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_project_gallery_images == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_gallery_images }}
                                                                @endif
                                                                {{ __('Gallery Images') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Gallery Images') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_additionl_specifications >= 1)
                                                            <li><i class="fal fa-check"></i>

                                                                @if ($package->number_of_project_additionl_specifications == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_additionl_specifications }}
                                                                @endif

                                                                {{ __('Additional Features') }}({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Additional Features') }}({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                    </ul>
                                                    <a href="{{ auth('vendor')->check() ? route('vendor.plan.extend.index') : route('vendor.login') }}"
                                                        class="btn btn-outline btn-lg rounded-pill w-100">
                                                        {{ __('Get Started') }} </a>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="p-3 text-center mb-30 w-100">
                                            <h3 class="mb-0"> {{ __('No Pricing Plan Found') }}</h3>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="tab-pane fade" id="forSell1">
                                <div class="row justify-content-center">
                                    @forelse ($packages as $package)
                                        @if ($package->term == 'lifetime')
                                            <div class="col-md-6 col-lg-4">
                                                <div class="pricing-item mb-30 radius-lg" data-aos="fade-up">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon color-primary"><i
                                                                class="{{ $package->icon }}"></i>
                                                        </div>
                                                        <div class="label">
                                                            <h3>{{ $package->title }}</h3>
                                                        </div>
                                                    </div>


                                                    <div class="d-flex align-items-center mt-15">
                                                        <span class="price">{{ symbolPrice($package->price) }}</span>
                                                        <span class="period text-capitalize">/
                                                            {{ __($package->term) }}</span>
                                                    </div>
                                                    <h5>{{ __("What's Included") }}</h5>
                                                    <ul class="item-list list-unstyled p-0 pricing-list">

                                                        @if ($package->number_of_agent >= 1)
                                                            <li><i class="fal fa-check"></i>

                                                                @if ($package->number_of_agent == 999999)
                                                                    {{ __('Unlimited') }} {{ __('Agents') }}
                                                                @elseif ($package->number_of_agent > 1)
                                                                    {{ $package->number_of_agent }} {{ __('Agents') }}
                                                                @else
                                                                    {{ $package->number_of_agent }} {{ __('Agent') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Agent') }} </li>
                                                        @endif

                                                        @if ($package->number_of_property >= 1)
                                                            <li><i class="fal fa-check"></i>


                                                                @if ($package->number_of_property == 999999)
                                                                    {{ __('Unlimited') }} {{ __('Properties') }}
                                                                @elseif ($package->number_of_property > 1)
                                                                    {{ $package->number_of_property }}
                                                                    {{ __('Properties') }}
                                                                @else
                                                                    {{ $package->number_of_property }}
                                                                    {{ __('Property') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Property') }}
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_property_gallery_images >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property_gallery_images == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_property_gallery_images }}
                                                                @endif
                                                                {{ __('Gallery Images') }} ({{ __('Per Property') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Gallery Images') }} ({{ __('Per Property') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_property_adittionl_specifications >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property_adittionl_specifications == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_property_adittionl_specifications }}
                                                                @endif
                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Property') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Property') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_projects >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_property == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @elseif ($package->number_of_property > 1)
                                                                    {{ $package->number_of_projects }}
                                                                    {{ __('Projects') }}
                                                                @else
                                                                    {{ $package->number_of_projects }}
                                                                    {{ __('Project') }}
                                                                @endif
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Project') }}
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_types >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_project_types == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_types }}
                                                                @endif
                                                                {{ __('Project Types') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Project Types') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_gallery_images >= 1)
                                                            <li><i class="fal fa-check"></i>
                                                                @if ($package->number_of_project_gallery_images == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_gallery_images }}
                                                                @endif
                                                                {{ __('Gallery Images') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Gallery Images') }} ({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                        @if ($package->number_of_project_additionl_specifications >= 1)
                                                            <li><i class="fal fa-check"></i>

                                                                @if ($package->number_of_project_additionl_specifications == 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    {{ $package->number_of_project_additionl_specifications }}
                                                                @endif

                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Project') }})
                                                            </li>
                                                        @else
                                                            <li class="disabled"><i class="fal fa-times"></i>
                                                                {{ __('Additional Features') }}
                                                                ({{ __('Per Project') }})
                                                            </li>
                                                        @endif

                                                    </ul>


                                                    <a href="{{ auth('vendor')->check() ? route('vendor.plan.extend.index') : route('vendor.login') }}"
                                                        class="btn btn-outline btn-lg rounded-pill w-100">
                                                        {{ __('Get Started') }} </a>


                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="p-3 text-center mb-30 w-100">
                                            <h3 class="mb-0"> {{ __('No Pricing Plan Found') }}</h3>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($secInfo->testimonial_section_status == 1)
        <section class="testimonial-area testimonial-2 with-radius pt-100 pb-70">
            <!-- Bg image -->
            <img class="lazyload bg-img" src="{{ asset('assets/img/' . $testimonialSecImage) }}">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-4">
                        <div class="content mb-30" data-aos="fade-up">
                            <div class="content-title">
                                <span class="subtitle">
                                    {{ $testimonialSecInfo->title }}</span>
                                <h2 class="title">
                                    {{ $testimonialSecInfo?->subtitle }} </h2>
                            </div>
                            <p class="text mb-30">
                                {{ $testimonialSecInfo?->content }}</p>
                            <!-- Slider pagination -->
                            <div class="swiper-pagination pagination-fraction" id="testimonial-slider-2-pagination">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8" data-aos="fade-up">
                        <div class="swiper" id="testimonial-slider-2">
                            <div class="swiper-wrapper">
                                @forelse ($testimonials as $testimonial)
                                    <div class="swiper-slide pb-30">
                                        <div class="slider-item">
                                            <div class="client-content">
                                                <div class="quote">
                                                    <p class="text mb-20">{{ $testimonial->comment }}</p>
                                                    <div class="ratings">
                                                        <div class="rate">
                                                            <div class="rating-icon"
                                                                style="width: {{ $testimonial->rating * 20 }}%"></div>
                                                        </div>
                                                        <span class="ratings-total">({{ $testimonial->rating }}) </span>
                                                    </div>
                                                </div>
                                                <div class="client-info d-flex align-items-center">
                                                    <div class="client-img position-static">
                                                        <div class="lazy-container rounded-pill ratio ratio-1-1">
                                                            @if (is_null($testimonial->image))
                                                                <img data-src="{{ asset('assets/img/profile.jpg') }}"
                                                                    class="lazyload">
                                                            @else
                                                                <img class="lazyload"
                                                                    data-src="{{ asset('assets/img/clients/' . $testimonial->image) }}">
                                                            @endif

                                                        </div>
                                                    </div>
                                                    <div class="content">
                                                        <h6 class="name">{{ $testimonial->name }}</h6>
                                                        <span class="designation">{{ $testimonial->occupation }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center mb-30 w-100">
                                        <h3 class="mb-0"> {{ __('No Testimonials Found') }}</h3>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($secInfo->brand_section_status == 1)
        <div class="sponsor ptb-100" data-aos="fade-up">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="swiper sponsor-slider">
                            <div class="swiper-wrapper">
                                @forelse ($brands as $brand)
                                    <div class="swiper-slide">
                                        <div class="item-single d-flex justify-content-center">
                                            <div class="sponsor-img">
                                                <a href="{{ $brand->url }}" target="_blank">
                                                    <img src=" {{ asset('assets/img/brands/' . $brand->image) }} ">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center mb-30 w-100">
                                        <h3 class="mb-0">{{ __('No Brands Found') }}</h3>
                                    </div>
                                @endforelse
                            </div>
                            <!-- Slider pagination -->
                            <div class="swiper-pagination position-static mt-30" id="sponsor-slider-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .ai-assistant-card-outer { max-width: 100%; min-width: 0; }
    .banner-ai-inline-wrap .ai-property-assistant-card { max-height: 85vh; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    @media (max-width: 767px) { .banner-ai-inline-wrap .ai-property-assistant-card { max-height: 80vh; } }
    .ai-property-assistant-card { background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%); border-radius: 16px; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,.25); }
    .ai-assistant-card-header { padding: 24px 70px 16px; text-align: center; position: relative; display: flex; align-items: center; justify-content: center; flex-wrap: wrap; }
    .ai-assistant-card-header-text { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; width: 100%; }
    .ai-assistant-location-btn { position: absolute; top: 16px; left: 16px; height: 40px; padding: 0 14px; border: 1px solid rgba(255,255,255,.3); background: rgba(255,255,255,.1); color: #fff; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 14px; transition: background .2s, border-color .2s; }
    .ai-assistant-location-btn:hover { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); }
    .ai-assistant-location-btn .ai-location-icon { flex-shrink: 0; }
    .ai-assistant-location-btn .ai-assistant-location-btn-text { white-space: nowrap; }
    .ai-assistant-fullscreen-toggle { position: absolute; top: 16px; right: 16px; width: 40px; height: 40px; border: 1px solid rgba(255,255,255,.3); background: rgba(255,255,255,.1); color: #fff; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: background .2s, border-color .2s; }
    .ai-assistant-fullscreen-toggle:hover { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); }
    .ai-assistant-fullscreen-overlay { position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .ai-assistant-fullscreen-overlay:not(.d-none) { display: flex !important; }
    .ai-assistant-fullscreen-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.6); }
    .ai-assistant-fullscreen-inner { position: relative; z-index: 1; width: 100%; max-width: 100%; max-height: 95vh; min-height: 400px; overflow: hidden; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
    .ai-assistant-fullscreen-inner .ai-property-assistant-card { max-height: 95vh; height: 100%; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .ai-assistant-card-pills { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 12px; }
    .ai-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 500; }
    .ai-pill-green { background: rgba(34, 197, 94, 0.25); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.4); }
    .ai-pill-blue { background: rgba(59, 130, 246, 0.25); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); }
    .ai-pill-icon { font-size: 14px; }
    .ai-assistant-card-title { color: #fff; font-size: 1.5rem; font-weight: 700; margin: 0 0 6px; line-height: 1.3; }
    .ai-assistant-card-subtitle { color: rgba(255,255,255,.7); font-size: 0.875rem; margin: 0 0 8px; line-height: 1.4; }
    .ai-assistant-online { display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #86efac; font-size: 13px; font-weight: 500; }
    .ai-online-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; animation: ai-pulse 1.5s ease-in-out infinite; }
    @keyframes ai-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    .ai-assistant-card-chat { padding: 16px 24px 24px; }
    .ai-assistant-inline-messages { max-height: 320px; overflow-y: auto; overflow-x: hidden; display: flex; flex-direction: column; gap: 14px; margin-bottom: 14px; padding-right: 6px; }
    .ai-assistant-inline-messages .ai-assistant-msg { max-width: 92%; padding: 12px 16px; border-radius: 14px; font-size: 14px; line-height: 1.55; word-wrap: break-word; white-space: pre-wrap; overflow-wrap: break-word; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
    .ai-assistant-inline-messages .ai-assistant-msg.assistant { align-self: flex-start; background: rgba(59, 130, 246, 0.22); color: #e0e7ff; border: 1px solid rgba(59, 130, 246, 0.35); }
    .ai-assistant-inline-messages .ai-assistant-msg.user { align-self: flex-end; background: rgba(30, 58, 95, 0.95); color: #fff; border: 1px solid rgba(255,255,255,.12); }
    .ai-assistant-inline-messages .ai-assistant-msg.error { align-self: flex-start; background: rgba(239, 68, 68, 0.22); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
    .ai-assistant-inline-messages .ai-assistant-msg .ai-msg-label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; opacity: .9; }
    .ai-assistant-inline-messages .ai-assistant-msg .ai-msg-body { display: block; }
    .ai-assistant-inline-messages .ai-inline-typing { font-style: italic; opacity: .85; }
    .ai-assistant-inline-quick { margin-bottom: 14px; }
    .ai-inline-quick-btn { width: 100%; padding: 12px 16px; background: rgba(30, 58, 95, 0.8); border: 1px solid rgba(255,255,255,.15); border-radius: 10px; color: #e0e7ff; font-size: 14px; cursor: pointer; text-align: left; transition: background .2s; }
    .ai-inline-quick-btn:hover { background: rgba(30, 58, 95, 1); }
    .ai-assistant-inline-examples { margin-bottom: 14px; }
    .ai-examples-label { color: rgba(255,255,255,.75); font-size: 12px; font-weight: 600; margin: 0 0 8px; }
    .ai-examples-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .ai-example-chip { padding: 8px 14px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.2); border-radius: 20px; color: #e0e7ff; font-size: 13px; line-height: 1.35; cursor: pointer; transition: background .2s, border-color .2s; text-align: left; max-width: 100%; }
    .ai-example-chip:hover { background: rgba(255,255,255,.15); border-color: rgba(255,255,255,.35); }
    .ai-assistant-inline-input-wrap { display: flex; gap: 8px; align-items: flex-end; }
    .ai-assistant-inline-input { flex: 1; padding: 12px 14px; border: 1px solid rgba(255,255,255,.2); border-radius: 10px; background: rgba(0,0,0,.2); color: #fff; font-size: 14px; resize: none; }
    .ai-assistant-inline-input::placeholder { color: rgba(255,255,255,.5); }
    .ai-assistant-inline-send { padding: 12px 20px; background: var(--color-primary, #BDA588); color: #fff; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
    .ai-assistant-inline-send:hover { opacity: .95; }
    .ai-assistant-inline-cta { color: rgba(255,255,255,.65); font-size: 13px; }
    .ai-assistant-cta-link { color: var(--color-primary, #BDA588); font-weight: 600; text-decoration: underline; }
    .ai-assistant-cta-link:hover { color: var(--color-primary, #BDA588); opacity: .9; }
    .ai-chat-property-results { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 10px; align-self: flex-start; max-width: 92%; }
    .ai-chat-property-card { width: calc(50% - 5px); min-width: 140px; background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.15); border-radius: 10px; overflow: hidden; }
    .ai-chat-property-card a { color: inherit; text-decoration: none; display: block; }
    .ai-chat-property-card-img { width: 100%; height: 90px; object-fit: cover; background: rgba(0,0,0,.3); }
    .ai-chat-property-card-body { padding: 8px 10px; }
    .ai-chat-property-card-title { font-size: 12px; font-weight: 600; color: #e0e7ff; margin: 0 0 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ai-chat-property-card-price { font-size: 13px; font-weight: 700; color: var(--color-primary, #BDA588); margin: 0 0 6px; }
    .ai-chat-property-card-link { font-size: 11px; color: rgba(255,255,255,.8); text-decoration: underline; }
    .ai-chat-property-view-all { margin-top: 8px; padding: 8px 12px; background: rgba(255,255,255,.1); border-radius: 8px; text-align: center; }
    .ai-chat-property-view-all a { color: var(--color-primary, #BDA588); font-weight: 600; font-size: 13px; }
    @media (max-width: 480px) { .ai-chat-property-card { width: 100%; } }
    </style>

    @if(config('ai.enabled'))
    <script>
    (function() {
        var showSearchBtn = document.getElementById('banner-show-search-form');
        var formWrap = document.getElementById('banner-filter-form-wrap');
        var askAssistantBtn = document.getElementById('banner-open-ai-inline');
        var aiInlineWrap = document.getElementById('banner-ai-inline-wrap');

        function showSearchForm() {
            if (aiInlineWrap) aiInlineWrap.classList.add('d-none');
            if (formWrap) { formWrap.classList.remove('d-none'); formWrap.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        }
        function showAiInline() {
            if (formWrap) formWrap.classList.add('d-none');
            if (aiInlineWrap) { aiInlineWrap.classList.remove('d-none'); aiInlineWrap.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        }

        if (showSearchBtn && formWrap) showSearchBtn.addEventListener('click', showSearchForm);
        if (askAssistantBtn && aiInlineWrap) askAssistantBtn.addEventListener('click', showAiInline);

        var fullscreenOverlay = document.getElementById('ai-assistant-fullscreen-overlay');
        var fullscreenInner = document.getElementById('ai-assistant-fullscreen-inner');
        var fullscreenBackdrop = document.getElementById('ai-assistant-fullscreen-backdrop');
        var fullscreenToggleBtn = document.getElementById('ai-assistant-fullscreen-toggle');
        var cardMovable = document.getElementById('ai-assistant-card-movable');
        var cardOriginalParent = cardMovable ? cardMovable.parentNode : null;

        function openAiFullscreen() {
            if (!cardMovable || !fullscreenInner || !cardOriginalParent) return;
            fullscreenInner.appendChild(cardMovable);
            if (fullscreenOverlay) { fullscreenOverlay.classList.remove('d-none'); fullscreenOverlay.setAttribute('aria-hidden', 'false'); }
            if (fullscreenToggleBtn) {
                fullscreenToggleBtn.querySelector('.ai-fullscreen-icon-expand').classList.add('d-none');
                fullscreenToggleBtn.querySelector('.ai-fullscreen-icon-collapse').classList.remove('d-none');
                fullscreenToggleBtn.setAttribute('title', '{{ __("Exit full screen") }}');
            }
            document.body.style.overflow = 'hidden';
        }
        function closeAiFullscreen() {
            if (!cardMovable || !cardOriginalParent) return;
            cardOriginalParent.appendChild(cardMovable);
            if (fullscreenOverlay) { fullscreenOverlay.classList.add('d-none'); fullscreenOverlay.setAttribute('aria-hidden', 'true'); }
            if (fullscreenToggleBtn) {
                fullscreenToggleBtn.querySelector('.ai-fullscreen-icon-expand').classList.remove('d-none');
                fullscreenToggleBtn.querySelector('.ai-fullscreen-icon-collapse').classList.add('d-none');
                fullscreenToggleBtn.setAttribute('title', '{{ __("View in full screen") }}');
            }
            document.body.style.overflow = '';
        }
        function toggleAiFullscreen() {
            if (fullscreenOverlay && fullscreenOverlay.classList.contains('d-none')) openAiFullscreen();
            else closeAiFullscreen();
        }
        if (fullscreenToggleBtn) fullscreenToggleBtn.addEventListener('click', toggleAiFullscreen);
        if (fullscreenBackdrop) fullscreenBackdrop.addEventListener('click', closeAiFullscreen);

        var chatUrl = '{{ route("ai.assistant.chat") }}';
        var csrf = '{{ csrf_token() }}';
        var inlineHistory = [];
        var inlineMessagesEl = document.getElementById('ai-assistant-inline-messages');
        var inlineInput = document.getElementById('ai-assistant-inline-input');
        var inlineSendBtn = document.getElementById('ai-assistant-inline-send');
        var inlineQuickBtn = document.getElementById('ai-assistant-header-location');

        function addInlineBubble(text, role) {
            if (!inlineMessagesEl) return;
            var div = document.createElement('div');
            div.className = 'ai-assistant-msg ' + role;
            var label = document.createElement('span');
            label.className = 'ai-msg-label';
            label.textContent = role === 'user' ? '{{ __("You") }}' : (role === 'assistant' ? '{{ __("Assistant") }}' : '');
            var body = document.createElement('span');
            body.className = 'ai-msg-body';
            body.textContent = text;
            if (label.textContent) div.appendChild(label);
            div.appendChild(body);
            inlineMessagesEl.appendChild(div);
            inlineMessagesEl.scrollTop = inlineMessagesEl.scrollHeight;
        }
        function addInlinePropertyCards(properties, searchUrl) {
            if (!inlineMessagesEl) return;
            var list = properties && properties.length ? properties : [];
            var wrap = document.createElement('div');
            wrap.className = 'ai-chat-property-results';
            list.forEach(function(p) {
                var priceText = p.price ? (p.price + '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '{{ __("Negotiable") }}';
                var card = document.createElement('div');
                card.className = 'ai-chat-property-card';
                var a = document.createElement('a');
                a.href = p.url || '#';
                a.target = '_blank';
                a.rel = 'noopener';
                var img = document.createElement('img');
                img.className = 'ai-chat-property-card-img';
                img.src = p.image || '';
                img.alt = (p.title || '').substring(0, 50);
                img.loading = 'lazy';
                var body = document.createElement('div');
                body.className = 'ai-chat-property-card-body';
                var title = document.createElement('p');
                title.className = 'ai-chat-property-card-title';
                title.textContent = p.title || '';
                var price = document.createElement('p');
                price.className = 'ai-chat-property-card-price';
                price.textContent = priceText;
                var link = document.createElement('span');
                link.className = 'ai-chat-property-card-link';
                link.textContent = '{{ __("View details") }}';
                body.appendChild(title);
                body.appendChild(price);
                body.appendChild(link);
                a.appendChild(img);
                a.appendChild(body);
                card.appendChild(a);
                wrap.appendChild(card);
            });
            if (searchUrl) {
                var viewAll = document.createElement('div');
                viewAll.className = 'ai-chat-property-view-all';
                var viewAllA = document.createElement('a');
                viewAllA.href = searchUrl;
                viewAllA.target = '_blank';
                viewAllA.rel = 'noopener';
                viewAllA.textContent = '{{ __("View all matching properties") }}';
                viewAll.appendChild(viewAllA);
                wrap.appendChild(viewAll);
            }
            inlineMessagesEl.appendChild(wrap);
            inlineMessagesEl.scrollTop = inlineMessagesEl.scrollHeight;
        }
        function setInlineTyping(show) {
            var sel = inlineMessagesEl ? inlineMessagesEl.querySelector('.ai-inline-typing') : null;
            if (show && !sel && inlineMessagesEl) {
                var el = document.createElement('div');
                el.className = 'ai-assistant-msg assistant ai-inline-typing';
                var body = document.createElement('span');
                body.className = 'ai-msg-body';
                body.textContent = '{{ __("Thinking...") }}';
                el.appendChild(body);
                inlineMessagesEl.appendChild(el);
                inlineMessagesEl.scrollTop = inlineMessagesEl.scrollHeight;
            } else if (!show && sel) sel.remove();
        }
        function sendInlineMessage(msg) {
            if (!msg || !msg.trim()) return;
            addInlineBubble(msg.trim(), 'user');
            inlineHistory.push({ role: 'user', content: msg.trim() });
            if (inlineInput) inlineInput.value = '';
            if (inlineSendBtn) inlineSendBtn.disabled = true;
            setInlineTyping(true);
            fetch(chatUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ message: msg.trim(), history: inlineHistory })
            }).then(function(r) { return r.json(); }).then(function(data) {
                setInlineTyping(false);
                if (data.success) {
                    addInlineBubble(data.message, 'assistant');
                    inlineHistory.push({ role: 'assistant', content: data.message });
                    if (data.search_url || (data.properties && data.properties.length)) {
                        addInlinePropertyCards(data.properties || [], data.search_url || null);
                    }
                } else {
                    addInlineBubble(data.error || 'Something went wrong.', 'error');
                }
            }).catch(function() {
                setInlineTyping(false);
                addInlineBubble('Unable to connect. Please try again.', 'error');
            }).finally(function() { if (inlineSendBtn) inlineSendBtn.disabled = false; });
        }
        if (inlineSendBtn && inlineInput) {
            inlineSendBtn.addEventListener('click', function() { sendInlineMessage(inlineInput.value); });
            inlineInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendInlineMessage(inlineInput.value); }
            });
        }
        if (inlineQuickBtn) {
            inlineQuickBtn.addEventListener('click', function() {
                sendInlineMessage('{{ __("Search properties near my location") }}');
            });
        }
        document.querySelectorAll('.ai-example-chip').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var q = this.getAttribute('data-query');
                if (q) sendInlineMessage(q);
            });
        });
    })();
    </script>
    @else
    <script>
    (function() {
        var showSearchBtn = document.getElementById('banner-show-search-form');
        var formWrap = document.getElementById('banner-filter-form-wrap');
        var askAssistantBtn = document.getElementById('banner-open-ai-inline');
        var aiInlineWrap = document.getElementById('banner-ai-inline-wrap');
        function showSearchForm() {
            if (aiInlineWrap) aiInlineWrap.classList.add('d-none');
            if (formWrap) { formWrap.classList.remove('d-none'); formWrap.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        }
        function showAiInline() {
            if (formWrap) formWrap.classList.add('d-none');
            if (aiInlineWrap) { aiInlineWrap.classList.remove('d-none'); aiInlineWrap.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        }
        if (showSearchBtn && formWrap) showSearchBtn.addEventListener('click', showSearchForm);
        if (askAssistantBtn && aiInlineWrap) askAssistantBtn.addEventListener('click', showAiInline);
    })();
    </script>
    @endif
@endsection
