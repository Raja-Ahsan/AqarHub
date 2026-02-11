@extends('vendors.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Property') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('vendor.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Property Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit Property') }}
                    @if ($property->type == 'residential')
                        {{ "(Residential)" }}
                    @else
                        {{ "(Commercial)" }}
                    @endif
                </a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Property') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('vendor.property_management.properties', ['language' => $defaultLang->code]) }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                    @php
                        $dContent = App\Models\Property\Content::where('property_id', $property->id)
                            ->where('language_id', $defaultLang->id)
                            ->first();
                        $slug = !empty($dContent) ? $dContent->slug : '';
                    @endphp
                    @if ($dContent)
                        <a class="btn btn-success btn-sm float-right mr-1 d-inline-block"
                            href="{{ route('frontend.property.details', ['slug' => $slug]) }}" target="_blank">
                            <span class="btn-label">
                                <i class="fas fa-eye"></i>
                            </span>
                            {{ __('Preview') }}
                        </a>
                    @endif
                    @if(config('ai.enabled') && $currentPackage && ($currentPackage->has_ai_features ?? false))
                        <button type="button" class="btn btn-outline-primary btn-sm float-right mr-1 d-inline-block" id="aiGenerateSocialCopyBtn" data-property-id="{{ $property->id }}">
                            <i class="fas fa-share-alt"></i> {{ __('Add on your social pages') }}
                        </button>
                    @endif

                </div>

                <div class="card-body">
                    @if (!empty($property->anomaly_checked_at) && !empty($property->anomaly_flags) && is_array($property->anomaly_flags) && count($property->anomaly_flags) > 0)
                        <div class="alert alert-warning mb-3">
                            <strong>{{ __('Review suggested') }}</strong>
                            <p class="mb-1 small">{{ __('Anomaly check (last run :date):', ['date' => $property->anomaly_checked_at->format('M j, Y H:i')]) }}</p>
                            <ul class="mb-0 pl-3">
                                @foreach ($property->anomaly_flags as $flag)
                                    <li>{{ is_array($flag) ? ($flag['message'] ?? '') : $flag }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-lg-10 offset-lg-1">
                            <div class="alert alert-danger pb-1 dis-none" id="carErrors">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <ul></ul>
                            </div>
                            <div class="col-lg-12">
                                <label for=""
                                    class="mb-2"><strong>{{ __('Gallery Images') . '*' }}</strong></label>
                                <div id="reload-slider-div">
                                    <div class="row">
                                        @if (count($galleryImages) > $currentPackage->number_of_property_gallery_images)
                                            <div class="col-lg-12">
                                                <div class="alert alert-danger">
                                                    {{ __('You can upload maximum ' . $currentPackage->number_of_property_gallery_images . ' gallery images under one property. You need to delete ' . count($galleryImages) - $currentPackage->number_of_property_gallery_images . ' gallery images from property.') }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <table class="table table-striped" id="imgtable">

                                                @foreach ($galleryImages as $item)
                                                    <tr class="trdb table-row" id="trdb{{ $item->id }}">
                                                        <td>
                                                            <div class="">
                                                                <img class="thumb-preview wf-150"
                                                                    src="{{ asset('assets/img/property/slider-images/' . $item->image) }}"
                                                                    alt="Ad Image">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="fa fa-times rmvbtndb"
                                                                data-indb="{{ $item->id }}"></i>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('vendor.property.imagesstore') }}" id="my-dropzone"
                                    enctype="multipart/formdata" class="dropzone create">
                                    @csrf
                                    <div class="fallback">
                                        <input name="file" type="file" multiple />
                                    </div>
                                    <input type="hidden" value="{{ $property->id }}" name="property_id">
                                </form>
                                <p class="em text-danger mb-0" id="errslider_images"></p>

                            </div>

                            <form id="carForm"
                                action="{{ route('vendor.property_management.update_property', $property->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">
                                <input type="hidden" name="type" value="{{ $property->type }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Thumbnail Image') . '*' }}</label>
                                            <br>
                                            <div class="thumb-preview">
                                                <img src="{{ $property->featured_image ? asset('assets/img/property/featureds/' . $property->featured_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="uploaded-img">
                                            </div>

                                            <div class="mt-3">
                                                <div role="button" class="btn btn-primary btn-sm upload-btn">
                                                    {{ __('Choose Image') }}
                                                    <input type="file" class="img-input" name="thumbnail">
                                                </div>
                                                @if(config('ai.enabled') && $currentPackage && ($currentPackage->has_ai_features ?? false))
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary ai-suggest-from-image-btn">
                                                        {{ __('Suggest from image') }}
                                                    </button>
                                                    <span class="ai-analyze-status text-muted small ml-2"></span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Floor Planning Image') }}</label>
                                            <br>
                                            <div class="thumb-preview remove">

                                                <img src="{{ !empty($property->floor_planning_image) ? asset('assets/img/property/plannings/' . $property->floor_planning_image) : asset('assets/img/noimage.jpg') }}"
                                                    alt="..." class="uploaded-img2">
                                                @if (!empty($property->floor_planning_image))
                                                    <i class="fas fa-times text-danger rmvflrImg"
                                                        data-indb="{{ $property->id }}"></i>
                                                @endif
                                            </div>

                                            <div class="mt-3">
                                                <div role="button" class="btn btn-primary btn-sm upload-btn">
                                                    {{ __('Choose Image') }}
                                                    <input type="file" class="img-input2" name="floor_planning_image">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Video Image') }}</label>
                                            <br>

                                            <div class="thumb-preview remove">

                                                <img src="{{ !empty($property->video_image) ? asset('assets/img/property/video/' . $property->video_image) : asset('assets/img/noimage.jpg') }}"
                                                    alt="..." class="uploaded-img3">
                                                @if (!empty($property->video_image))
                                                    <i class="fas fa-times text-danger rmvvdoImg"
                                                        data-indb="{{ $property->id }}"></i>
                                                @endif
                                            </div>

                                            <div class="mt-3">
                                                <div role="button" class="btn btn-primary btn-sm upload-btn">
                                                    {{ __('Choose Image') }}
                                                    <input type="file" class="img-input3" name="video_image">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{ __('Video Url') }} </label>
                                            <input type="text" class="form-control" name="video_url"
                                                placeholder="Enter Speed" value="{{ $property->video_url }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{ __('Purpose') }}*</label>

                                            <select name="purpose" class="form-control">
                                                <option disabled> {{ __('Select a Purpose') }} </option>
                                                <option value="rent" @if ($property->purpose == 'rent') 'selected' @endif>
                                                    {{ __("Rent") }}
                                                </option>
                                                <option value="sale" @if ($property->purpose == 'sale') 'selected' @endif>
                                                    {{ __("Sale") }}
                                                </option>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group ">
                                            <label>{{ __('Category') }} *</label>
                                            <select name="category_id" class="form-control category">
                                                <option disabled selected>
                                                    {{ __('Select a Category') }}
                                                </option>

                                                @foreach ($propertyCategories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ $property->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->categoryContent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group ">

                                            <label>{{ __('Country') }} *</label>
                                            <select name="country_id"
                                                class="form-control country js-example-basic-single3">
                                                <option disabled selected>{{ __('Select Country') }}
                                                </option>

                                                @foreach ($propertyCountries as $country)
                                                    <option value="{{ $country->id }}"
                                                        {{ $property->country_id == $country->id ? 'selected' : '' }}>
                                                        {{ $country->countryContent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 state"
                                        @if (empty($property->state_id)) style="display:none;"@else style="display:block !important;" @endif>
                                        <div class="form-group">

                                            <label>{{ __('State') }} *</label>
                                            <select onchange="getCities(event)" name="state_id js-example-basic-single3"
                                                class="form-control  state_id states">
                                                <option disabled>{{ __('Select State') }}
                                                </option>
                                                @if ($property->state_id)
                                                    @foreach ($propertyStates as $state)
                                                        <option value="{{ $state->id }}"
                                                            {{ $property->state_id == $state->id ? 'selected' : '' }}>
                                                            {{ $state?->stateContent->name }}</option>
                                                    @endforeach
                                                @endif


                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 city"
                                        @if (empty($property->city_id)) style="display:none;"@else style="display:block;" @endif>
                                        <div class="form-group ">

                                            <label>{{ __('City') }} *</label>
                                            <select name="city_id" class="form-control city_id js-example-basic-single3">
                                                <option value="" disabled>{{ __('Select City') }}
                                                </option>
                                                @if ($property->city_id)
                                                    @foreach ($propertyCities as $city)
                                                        <option value="{{ $property->city_id }}"
                                                            {{ $property->city_id == $city->id ? 'selected' : '' }}>
                                                            {{ $city?->cityContent->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="">{{ __('Amenities') }}*</label>
                                            <select name="amenities[]" class="form-control js-example-basic-single2"
                                                multiple="multiple">
                                                <option value="" disabled>
                                                    {{ __('Please Select Amenities') }}
                                                </option>
                                                @foreach ($amenities as $amenity)
                                                    <option value="{{ $amenity->id }}"
                                                        @foreach ($propertyAmenities as $propertyAmenity)
                                                            {{ $propertyAmenity->amenity_id == $amenity->id ? 'selected' : '' }} @endforeach>
                                                        {{ $amenity->amenityContent->name }}</option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{ __('Price') . ' (' . $settings->base_currency_text . ')' }} </label>
                                            <input type="number" class="form-control" name="price"
                                                placeholder="Enter Current Price" value="{{ $property->price }}">
                                            @if(config('ai.enabled') && $currentPackage && ($currentPackage->has_ai_features ?? false))
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-1 ai-suggest-price-btn" data-property-id="{{ $property->id }}">
                                                {{ __('Suggest price') }}
                                            </button>
                                            <div id="ai-suggest-price-box" class="mt-2 p-2 small border rounded bg-light" style="display:none;">
                                                <div class="ai-suggest-price-range font-weight-bold"></div>
                                                <div class="ai-suggest-price-justification text-muted mt-1"></div>
                                                <p class="ai-suggest-price-disclaimer text-warning mb-0 mt-1 small"></p>
                                            </div>
                                            @endif
                                            <p class="text-warning">
                                                {{ __('If you leave it blank, price will be negotiable.') }}
                                            </p>
                                        </div>
                                    </div>


                                    @if ($property->type == 'residential')
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>{{ __('Beds') }} *</label>
                                                <input type="text" class="form-control" name="beds"
                                                    value="{{ $property->beds }}" placeholder="Enter number of bed">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>{{ __('Baths') }} *</label>
                                                <input type="text" class="form-control" name="bath"
                                                    value="{{ $property->bath }}" placeholder="Enter number of bath">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{ __('Area (sqft)') }} *</label>
                                            <input type="text" class="form-control" name="area"
                                                value="{{ $property->area }}" placeholder="Enter area (sqft) ">
                                        </div>
                                    </div>


                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{ __('Status') }} *</label>
                                            <select name="status" id="" class="form-control">
                                                <option {{ $property->status == 1 ? 'selected' : '' }} value="1">
                                                    {{ __('Active') }}</option>
                                                <option {{ $property->status == 0 ? 'selected' : '' }} value="0">
                                                    {{ __('Inactive') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{ __('Latitude') }} *</label>
                                            <input type="text" class="form-control" value="{{ $property->latitude }}"
                                                name="latitude" placeholder="Enter Latitude">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>{{ __('Longitude') }} *</label>
                                            <input type="text" class="form-control"
                                                value="{{ $property->longitude }}" name="longitude"
                                                placeholder="Enter Longitude">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="">{{ __('Agentss') }}</label>
                                            <select name="agent_id" class="form-control js-example-basic-single3">
                                                <option value="" selected>{{ __('Select Agent') }}</option>
                                                @foreach ($agents as $agent)
                                                    <option {{ $property->agent_id == $agent->id ? 'selected' : '' }}
                                                        value="{{ $agent->id }}">
                                                        {{ $agent->username }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-warning">
                                                {{ __('if you do not select any agent, then this property will be listed under you') }}
                                            </p>
                                        </div>

                                    </div>

                                </div>

                                <div id="accordion" class="mt-3">
                                    @foreach ($languages as $language)
                                        @php
                                            $peopertyContent = App\Models\Property\Content::where(
                                                'property_id',
                                                $property->id,
                                            )
                                                ->where('language_id', $language->id)
                                                ->first();

                                        @endphp
                                        <div class="version">
                                            <div class="version-header" id="heading{{ $language->id }}">
                                                <h5 class="mb-0">
                                                    <button type="button" class="btn btn-link" data-toggle="collapse"
                                                        data-target="#collapse{{ $language->id }}"
                                                        aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}"
                                                        aria-controls="collapse{{ $language->id }}">
                                                        {{ $language->name . __(' Language') }}
                                                        {{ $language->is_default == 1 ? '(Default)' : '' }}
                                                    </button>
                                                </h5>
                                            </div>

                                            <div id="collapse{{ $language->id }}"
                                                class="collapse {{ $language->is_default == 1 ? 'show' : '' }}"
                                                aria-labelledby="heading{{ $language->id }}" data-parent="#accordion">
                                                <div class="version-body">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Title*') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="{{ $language->code }}_title"
                                                                    placeholder="Enter Title"
                                                                    value="{{ $peopertyContent ? $peopertyContent->title : '' }}">
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Address') . '*' }} </label>
                                                                <input type="text"
                                                                    name="{{ $language->code }}_address"
                                                                    placeholder="Enter Address"
                                                                    value="{{ @$peopertyContent->address }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Description') }} *</label>
                                                                @if(config('ai.enabled') && $currentPackage && ($currentPackage->has_ai_features ?? false))
                                                                <div class="mb-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary ai-generate-desc-btn"
                                                                        data-title-name="{{ $language->code }}_title"
                                                                        data-address-name="{{ $language->code }}_address"
                                                                        data-desc-id="{{ $language->code }}_description">
                                                                        {{ __('Generate with AI') }}
                                                                    </button>
                                                                    @if($language->is_default != 1)
                                                                    <button type="button" class="btn btn-sm btn-outline-info ai-translate-btn ml-1"
                                                                        data-default-lang="{{ $languages->where('is_default', 1)->first()->code ?? 'en' }}"
                                                                        data-target-lang="{{ $language->name }}"
                                                                        data-target-title-name="{{ $language->code }}_title"
                                                                        data-target-desc-id="{{ $language->code }}_description">
                                                                        {{ __('Translate from default') }}
                                                                    </button>
                                                                    @endif
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary ai-check-compliance-btn ml-1"
                                                                        data-desc-id="{{ $language->code }}_description">
                                                                        {{ __('Check compliance') }}
                                                                    </button>
                                                                    <span class="ai-generate-status text-muted small ml-2"></span>
                                                                </div>
                                                                <div class="ai-compliance-result mb-2" style="display:none;"></div>
                                                                @endif
                                                                <textarea class="form-control summernote " id="{{ $language->code }}_description"
                                                                    name="{{ $language->code }}_description" data-height="300">{{ @$peopertyContent->description }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Meta Keywords') }} *</label>
                                                                <input class="form-control"
                                                                    name="{{ $language->code }}_meta_keyword"
                                                                    placeholder="Enter Meta Keywords"
                                                                    data-role="tagsinput"
                                                                    value="{{ $peopertyContent ? $peopertyContent->meta_keyword : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Meta Description') }} *</label>
                                                                <textarea class="form-control" name="{{ $language->code }}_meta_description" rows="5"
                                                                    placeholder="Enter Meta Description">{{ $peopertyContent ? $peopertyContent->meta_description : '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col">
                                                            @php $currLang = $language; @endphp

                                                            @foreach ($languages as $language)
                                                                @continue($language->id == $currLang->id)

                                                                <div class="form-check py-0">
                                                                    <label class="form-check-label">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            onchange="cloneInput('collapse{{ $currLang->id }}', 'collapse{{ $language->id }}', event)">
                                                                        <span
                                                                            class="form-check-sign">{{ __('Clone for') }}
                                                                            <strong
                                                                                class="text-capitalize text-secondary">{{ $language->name }}</strong>
                                                                            {{ __('language') }}</span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="row">
                                    <div class="col-lg-12" id="variation_pricing">
                                        <h4 for="">{{ __('Additional Features (Optional)') }}</h4>
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Label') }}</th>
                                                    <th>{{ __('Value') }}</th>
                                                    <th>
                                                        <a href="javascrit:void(0)"
                                                            class="btn  btn-sm btn-success addRow"><i
                                                                class="fas fa-plus-circle"></i>
                                                        </a>
                                                    </th>
                                                </tr>
                                            <tbody id="tbody">

                                                @if (count($specifications) > 0)
                                                    @foreach ($specifications as $specification)
                                                        <tr>
                                                            <td>
                                                                @foreach ($languages as $language)
                                                                    @php
                                                                        $sp_content = App\Models\Property\SpacificationCotent::where(
                                                                            [
                                                                                ['language_id', $language->id],
                                                                                [
                                                                                    'property_spacification_id',
                                                                                    $specification->id,
                                                                                ],
                                                                            ],
                                                                        )->first();
                                                                    @endphp
                                                                    <div
                                                                        class="form-group  {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                        <input type="text"
                                                                            name="{{ $language->code }}_label[]"
                                                                            value="{{ @$sp_content->label }}"
                                                                            class="form-control"
                                                                            placeholder="Label ({{ $language->name }})">
                                                                    </div>
                                                                @endforeach
                                                            </td>
                                                            <td>
                                                                @foreach ($languages as $language)
                                                                    @php
                                                                        $sp_content = App\Models\Property\SpacificationCotent::where(
                                                                            [
                                                                                ['language_id', $language->id],
                                                                                [
                                                                                    'property_spacification_id',
                                                                                    $specification->id,
                                                                                ],
                                                                            ],
                                                                        )->first();
                                                                    @endphp
                                                                    <div
                                                                        class="form-group  {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                        <input type="text"
                                                                            name="{{ $language->code }}_value[]"
                                                                            value="{{ @$sp_content->value }}"
                                                                            class="form-control"
                                                                            placeholder="Value ({{ $language->name }})">
                                                                    </div>
                                                                @endforeach
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0)"
                                                                    data-specification="{{ $specification->id }}"
                                                                    class="btn  btn-sm btn-danger deleteSpecification">
                                                                    <i class="fas fa-minus"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td>
                                                            @foreach ($languages as $language)
                                                                <div
                                                                    class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                    <input type="text"
                                                                        name="{{ $language->code }}_label[]"
                                                                        class="form-control"
                                                                        placeholder="Label ({{ $language->name }})">
                                                                </div>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach ($languages as $language)
                                                                <div
                                                                    class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                                                    <input type="text"
                                                                        name="{{ $language->code }}_value[]"
                                                                        class="form-control"
                                                                        placeholder="Value ({{ $language->name }})">
                                                                </div>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)"
                                                                class="btn btn-danger  btn-sm deleteRow">
                                                                <i class="fas fa-minus"></i></a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            </thead>
                                        </table>
                                    </div>
                                </div>

                                <div id="sliders"></div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" id="PropertySubmit" class="btn btn-primary">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(config('ai.enabled') && $currentPackage && ($currentPackage->has_ai_features ?? false))
    <div class="modal fade" id="socialCopyModal" tabindex="-1" role="dialog" aria-labelledby="socialCopyModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="socialCopyModalTitle">{{ __('Add on your social pages') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">{{ __('Copy the text below or use "Add post" to publish to your connected accounts with text, image/video, and hashtags.') }}</p>
                    @php
                        $sc = $social_connections ?? collect();
                        $fbConnected = $sc->where('platform','facebook')->first() && !$sc->where('platform','facebook')->first()->isExpired();
                        $liConnected = $sc->where('platform','linkedin')->first() && !$sc->where('platform','linkedin')->first()->isExpired();
                        $igConnected = $sc->where('platform','instagram')->first() && !$sc->where('platform','instagram')->first()->isExpired();
                        $tiktokConnected = $sc->where('platform','tiktok')->first() && !$sc->where('platform','tiktok')->first()->isExpired();
                        $twitterConnected = $sc->where('platform','twitter')->first() && !$sc->where('platform','twitter')->first()->isExpired();
                        $featuredUrl = $property->featured_image ? asset('assets/img/property/featureds/' . $property->featured_image) : '';
                        $galleryFirst = isset($galleryImages) && $galleryImages->isNotEmpty() ? asset('assets/img/property/slider-images/' . $galleryImages->first()->image) : '';
                        $videoThumbUrl = !empty($property->video_image) ? asset('assets/img/property/video/' . $property->video_image) : '';
                    @endphp
                    <input type="hidden" id="socialCopyFeaturedImageUrl" value="{{ $featuredUrl }}">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">{{ __('Media for post (image or video thumbnail)') }}</label>
                        <select class="form-control" id="socialCopyMediaSelect">
                            <option value="{{ $featuredUrl }}" data-url="{{ $featuredUrl }}">{{ __('Featured image') }}</option>
                            @if($galleryFirst)
                            <option value="{{ $galleryFirst }}" data-url="{{ $galleryFirst }}">{{ __('First gallery image') }}</option>
                            @endif
                            @if($videoThumbUrl)
                            <option value="{{ $videoThumbUrl }}" data-url="{{ $videoThumbUrl }}">{{ __('Video thumbnail') }}</option>
                            @endif
                        </select>
                        <small class="text-muted">{{ __('Choose which image to attach to the post.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Facebook') }}</label>
                        <div class="input-group">
                            <textarea class="form-control" id="socialCopyFacebook" rows="3" readonly></textarea>
                            <div class="input-group-append">
                                @if($fbConnected)
                                <button type="button" class="btn btn-primary btn-add-post-social" data-platform="facebook" data-text-target="socialCopyFacebook">{{ __('Add post') }}</button>
                                @else
                                <span class="btn btn-secondary disabled">{{ __('Connect Facebook in Settings') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Instagram') }}</label>
                        <div class="input-group">
                            <textarea class="form-control" id="socialCopyInstagram" rows="2" readonly></textarea>
                            <div class="input-group-append">
                                @if($igConnected)
                                <button type="button" class="btn btn-primary btn-add-post-social btn-add-post-instagram" data-platform="instagram" data-text-target="socialCopyInstagram">{{ __('Add post') }}</button>
                                @else
                                <span class="btn btn-secondary disabled">{{ __('Connect Instagram in Settings') }}</span>
                                @endif
                            </div>
                        </div>
                        <small class="text-muted">{{ __('Uses selected media above.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('LinkedIn') }}</label>
                        <div class="input-group">
                            <textarea class="form-control" id="socialCopyLinkedin" rows="3" readonly></textarea>
                            <div class="input-group-append">
                                @if($liConnected)
                                <button type="button" class="btn btn-primary btn-add-post-social" data-platform="linkedin" data-text-target="socialCopyLinkedin">{{ __('Add post') }}</button>
                                @else
                                <span class="btn btn-secondary disabled">{{ __('Connect LinkedIn in Settings') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Twitter | X') }}</label>
                        <div class="input-group">
                            <textarea class="form-control" id="socialCopyTwitter" rows="2" readonly maxlength="280" placeholder="280 {{ __('characters max') }}"></textarea>
                            <div class="input-group-append">
                                @if($twitterConnected)
                                <button type="button" class="btn btn-primary btn-add-post-social" data-platform="twitter" data-text-target="socialCopyTwitter">{{ __('Add post') }}</button>
                                @else
                                <span class="btn btn-secondary disabled">{{ __('Connect X in Settings') }}</span>
                                @endif
                            </div>
                        </div>
                        <small class="text-muted">{{ __('280 characters max for X.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('TikTok') }}</label>
                        <div class="input-group">
                            <textarea class="form-control" id="socialCopyTiktok" rows="2" readonly placeholder="{{ __('Caption for TikTok (same as Instagram)') }}"></textarea>
                            <div class="input-group-append">
                                @if($tiktokConnected)
                                <button type="button" class="btn btn-dark btn-add-post-tiktok">{{ __('Add post') }}</button>
                                @else
                                <span class="btn btn-secondary disabled">{{ __('Connect TikTok in Settings') }}</span>
                                @endif
                            </div>
                        </div>
                        <small class="text-muted">{{ __('Caption is copied; add your video on TikTok upload page.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Hashtags') }}</label>
                        <textarea class="form-control" id="socialCopyHashtags" rows="1" readonly></textarea>
                        <small class="text-muted">{{ __('Included in each Add post above.') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@php
    $languages = App\Models\Language::get();
    $labels = '';
    $values = '';
    foreach ($languages as $language) {
        $label_name = $language->code . '_label[]';
        $value_name = $language->code . '_value[]';
        if ($language->direction == 1) {
            $direction = 'form-group rtl text-right';
        } else {
            $direction = 'form-group';
        }

        $labels .=
            "<div class='$direction'><input type='text' name='" .
            $label_name .
            "' class='form-control' placeholder='Label ($language->name)'></div>";
        $values .= "<div class='$direction'><input type='text' name='$value_name' class='form-control' placeholder='Value ($language->name)'></div>";
    }
@endphp

@section('script')
    <script>
        var labels = "{!! $labels !!}";
        var values = "{!! $values !!}";
        var stateUrl = "{{ route('vendor.property_specification.get_state_cities') }}";
        var cityUrl = "{{ route('vendor.property_specification.get_cities') }}";
        var storeUrl = "{{ route('vendor.property.imagesupdate') }}";
        var removeUrl = "{{ route('vendor.property.imagermv') }}";
        var rmvdbUrl = "{{ route('vendor.property.imgdbrmv') }}";
        var specificationRmvUrl = "{{ route('vendor.property_management.specification_delete') }}"
        var galleryImages = {{ $currentPackage->number_of_property_gallery_images - count($galleryImages) }};
    </script>
    <script type="text/javascript" src="{{ asset('assets/js/admin-dropzone.js') }}"></script>
    <script src="{{ asset('assets/js/property.js') }}"></script>
    @if(config('ai.enabled') && $currentPackage && ($currentPackage->has_ai_features ?? false))
    <script>
    var defaultLang = '{{ $languages->where("is_default", 1)->first()->code ?? "en" }}';
    var aiAnalyzeUrl = '{{ route("ai.assistant.analyze_image") }}';
    var aiTranslateUrl = '{{ route("ai.assistant.translate") }}';
    var aiCheckComplianceUrl = '{{ route("ai.assistant.check_compliance") }}';
    var aiSuggestPriceUrl = '{{ route("ai.assistant.suggest_price") }}';
    var aiCsrf = '{{ csrf_token() }}';
    function setEditorContent(editorId, content) {
        if (typeof tinymce !== 'undefined') {
            var ed = tinymce.get(editorId);
            if (ed) { ed.setContent(content || ''); return; }
        }
        if (typeof $ !== 'undefined' && $('#' + editorId).length) {
            try { $('#' + editorId).summernote('code', content || ''); } catch (e) {}
        }
    }
    function getEditorContent(editorId) {
        if (typeof tinymce !== 'undefined') {
            var ed = tinymce.get(editorId);
            if (ed) return ed.getContent();
        }
        try { if (typeof $ !== 'undefined' && $('#' + editorId).length) return $('#' + editorId).summernote('code'); } catch (e) {}
        return '';
    }
    (function() {
        var url = "{{ route('ai.assistant.generate_description') }}";
        var token = "{{ csrf_token() }}";
        function getFormContextForAi() {
            var ctx = {};
            if (typeof $ === 'undefined') return ctx;
            var sel = function(name) { var o = $('select[name="' + name + '"] option:selected'); return (o.length && o.val()) ? o.text().trim() : ''; };
            var inp = function(name) { var e = document.querySelector('input[name="' + name + '"], textarea[name="' + name + '"]'); return e ? (e.value || '').trim() : ''; };
            if (sel('purpose')) ctx.purpose = sel('purpose');
            if (sel('category_id')) ctx.category = sel('category_id');
            if (sel('country_id')) ctx.country = sel('country_id');
            if (sel('state_id')) ctx.state = sel('state_id');
            if (sel('city_id')) ctx.city = sel('city_id');
            var amenSel = $('select[name="amenities[]"] option:selected');
            if (amenSel.length) { ctx.amenities = amenSel.map(function() { return $(this).text().trim(); }).get().filter(Boolean).join(', '); }
            if (inp('price')) ctx.price = inp('price');
            if (inp('video_url')) ctx.video_url = inp('video_url');
            if (inp('beds')) ctx.beds = inp('beds');
            if (inp('bath')) ctx.bath = inp('bath');
            if (inp('area')) ctx.area = inp('area');
            if (inp('type')) ctx.type = inp('type');
            return ctx;
        }
        document.querySelectorAll('.ai-generate-desc-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var titleName = this.getAttribute('data-title-name');
                var addressName = this.getAttribute('data-address-name');
                var descId = this.getAttribute('data-desc-id');
                var title = (document.querySelector('input[name="' + titleName + '"]') || {}).value || '';
                var address = (document.querySelector('input[name="' + addressName + '"]') || {}).value || '';
                var statusEl = this.closest('.mb-2').querySelector('.ai-generate-status');
                if (!title.trim()) { statusEl.textContent = '{{ __("Enter a title first") }}'; return; }
                statusEl.textContent = '{{ __("Generating...") }}';
                this.disabled = true;
                var payload = { title: title, location: address, features: '' };
                if (typeof getFormContextForAi === 'function') { var ctx = getFormContextForAi(); for (var k in ctx) if (ctx[k]) payload[k] = ctx[k]; }
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                }).then(function(r) { return r.json(); }).then(function(data) {
                    statusEl.textContent = data.success ? '{{ __("Done") }}' : (data.error || '');
                    if (data.success) {
                        if (data.description) setEditorContent(descId, data.description);
                        var langCode = descId.replace('_description', '');
                        if (data.meta_keywords) {
                            var kwEl = document.querySelector('input[name="' + langCode + '_meta_keyword"]');
                            if (kwEl) {
                                if (typeof $ !== 'undefined' && $(kwEl).data('role') === 'tagsinput') {
                                    $(kwEl).tagsinput('removeAll');
                                    data.meta_keywords.split(',').forEach(function(t) { var s = t.trim(); if (s) $(kwEl).tagsinput('add', s); });
                                } else { kwEl.value = data.meta_keywords; }
                            }
                        }
                        if (data.meta_description) {
                            var mdEl = document.querySelector('textarea[name="' + langCode + '_meta_description"]');
                            if (mdEl) mdEl.value = data.meta_description;
                        }
                    }
                }).catch(function() { statusEl.textContent = '{{ __("Error") }}'; }).finally(function() { btn.disabled = false; });
            });
        });
        var suggestBtn = document.querySelector('.ai-suggest-from-image-btn');
        if (suggestBtn) {
            suggestBtn.addEventListener('click', function() {
                var input = document.querySelector('input.img-input[name="thumbnail"]');
                var file = input && input.files && input.files[0];
                var imgEl = document.querySelector('.thumb-preview .uploaded-img');
                var currentSrc = imgEl && imgEl.src;
                var statusEl = suggestBtn.closest('.mt-2').querySelector('.ai-analyze-status');
                if (!file && (!currentSrc || currentSrc.indexOf('noimage') !== -1)) {
                    statusEl.textContent = '{{ __("Choose an image first") }}';
                    return;
                }
                statusEl.textContent = '{{ __("Analyzing...") }}';
                suggestBtn.disabled = true;
                if (file && file.type.match(/^image\//)) {
                    var fd = new FormData();
                    fd.append('image', file);
                    fd.append('_token', aiCsrf);
                    fetch(aiAnalyzeUrl, { method: 'POST', body: fd })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            statusEl.textContent = data.success ? '{{ __("Done") }}' : (data.error || '');
                            if (data.success) {
                                if (data.description) setEditorContent(defaultLang + '_description', data.description);
                                if (data.tags && data.tags.length) {
                                    var kw = document.querySelector('input[name="' + defaultLang + '_meta_keyword"]');
                                    if (kw && typeof $ !== 'undefined' && $(kw).data('role') === 'tagsinput') {
                                        data.tags.forEach(function(t) { var s = (t && t.trim) ? t.trim() : String(t).trim(); if (s) $(kw).tagsinput('add', s); });
                                    } else if (kw) { kw.value = (kw.value ? kw.value + ',' : '') + data.tags.join(','); }
                                }
                            }
                        })
                        .catch(function() { statusEl.textContent = '{{ __("Error") }}'; })
                        .finally(function() { suggestBtn.disabled = false; });
                } else {
                    fetch(aiAnalyzeUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': aiCsrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ image_url: currentSrc })
                    }).then(function(r) { return r.json(); })
                        .then(function(data) {
                            statusEl.textContent = data.success ? '{{ __("Done") }}' : (data.error || '');
                            if (data.success) {
                                if (data.description) setEditorContent(defaultLang + '_description', data.description);
                                if (data.tags && data.tags.length) {
                                    var kw = document.querySelector('input[name="' + defaultLang + '_meta_keyword"]');
                                    if (kw && typeof $ !== 'undefined' && $(kw).data('role') === 'tagsinput') {
                                        data.tags.forEach(function(t) { var s = (t && t.trim) ? t.trim() : String(t).trim(); if (s) $(kw).tagsinput('add', s); });
                                    } else if (kw) { kw.value = (kw.value ? kw.value + ',' : '') + data.tags.join(','); }
                                }
                            }
                        })
                        .catch(function() { statusEl.textContent = '{{ __("Error") }}'; })
                        .finally(function() { suggestBtn.disabled = false; });
                }
            });
        }
        document.querySelectorAll('.ai-translate-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var defaultCode = this.getAttribute('data-default-lang');
                var targetLang = this.getAttribute('data-target-lang');
                var targetTitleName = this.getAttribute('data-target-title-name');
                var targetDescId = this.getAttribute('data-target-desc-id');
                var titleEl = document.querySelector('input[name="' + defaultCode + '_title"]');
                var titleVal = titleEl ? titleEl.value : '';
                var descVal = getEditorContent(defaultCode + '_description');
                var statusEl = btn.closest('.mb-2').querySelector('.ai-generate-status');
                if (!titleVal.trim() && !descVal.trim()) { statusEl.textContent = '{{ __("Fill default language first") }}'; return; }
                statusEl.textContent = '{{ __("Translating...") }}';
                btn.disabled = true;
                var pending = (titleVal.trim() ? 1 : 0) + (descVal.trim() ? 1 : 0);
                function onDone() { pending--; if (pending <= 0) { statusEl.textContent = '{{ __("Done") }}'; btn.disabled = false; } }
                if (titleVal.trim()) {
                    fetch(aiTranslateUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': aiCsrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ text: titleVal, target_language: targetLang })
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        if (data.success && data.translation) {
                            var t = document.querySelector('input[name="' + targetTitleName + '"]');
                            if (t) t.value = data.translation;
                        }
                        onDone();
                    }).catch(function() { onDone(); });
                }
                if (descVal.trim()) {
                    fetch(aiTranslateUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': aiCsrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ text: descVal, target_language: targetLang })
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        if (data.success && data.translation) setEditorContent(targetDescId, data.translation);
                        onDone();
                    }).catch(function() { onDone(); });
                }
            });
        });
        document.querySelectorAll('.ai-check-compliance-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var descId = this.getAttribute('data-desc-id');
                var descVal = getEditorContent(descId);
                var resultEl = this.closest('.form-group').querySelector('.ai-compliance-result');
                var statusEl = this.closest('.mb-2').querySelector('.ai-generate-status');
                if (!resultEl) return;
                resultEl.style.display = 'none';
                resultEl.innerHTML = '';
                if (!descVal || !descVal.trim()) {
                    if (statusEl) statusEl.textContent = '{{ __("Enter a description first") }}';
                    return;
                }
                if (statusEl) statusEl.textContent = '{{ __("Checking...") }}';
                btn.disabled = true;
                fetch(aiCheckComplianceUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': aiCsrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ description: descVal })
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (statusEl) statusEl.textContent = '';
                    btn.disabled = false;
                    resultEl.style.display = 'block';
                    if (!data.success) {
                        resultEl.className = 'ai-compliance-result mb-2 alert alert-danger';
                        resultEl.innerHTML = data.error || '{{ __("Check failed.") }}';
                        return;
                    }
                    if (data.compliant) {
                        resultEl.className = 'ai-compliance-result mb-2 alert alert-success';
                        resultEl.innerHTML = '<strong>{{ __("Compliance check") }}</strong>: {{ __("No issues found.") }}' + (data.summary ? ' ' + data.summary : '');
                    } else {
                        resultEl.className = 'ai-compliance-result mb-2 alert alert-warning';
                        var html = '<strong>{{ __("Compliance check") }}</strong>: ' + (data.summary || '{{ __("Some wording may need review.") }}');
                        if (data.warnings && data.warnings.length) {
                            html += '<ul class="mb-0 mt-2">';
                            data.warnings.forEach(function(w) { html += '<li>' + (typeof w === 'string' ? w : '').replace(/</g, '&lt;') + '</li>'; });
                            html += '</ul>';
                        }
                        resultEl.innerHTML = html;
                    }
                }).catch(function() {
                    if (statusEl) statusEl.textContent = '';
                    btn.disabled = false;
                    resultEl.style.display = 'block';
                    resultEl.className = 'ai-compliance-result mb-2 alert alert-danger';
                    resultEl.innerHTML = '{{ __("Request failed.") }}';
                });
            });
        });
        document.querySelectorAll('.ai-suggest-price-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var propertyId = this.getAttribute('data-property-id');
                var box = document.getElementById('ai-suggest-price-box');
                var rangeEl = box ? box.querySelector('.ai-suggest-price-range') : null;
                var justEl = box ? box.querySelector('.ai-suggest-price-justification') : null;
                var discEl = box ? box.querySelector('.ai-suggest-price-disclaimer') : null;
                if (!box || !rangeEl) return;
                box.style.display = 'none';
                rangeEl.textContent = '{{ __("Loading...") }}';
                justEl.textContent = '';
                if (discEl) discEl.textContent = '';
                btn.disabled = true;
                var payload = {};
                if (propertyId) payload.property_id = parseInt(propertyId, 10);
                fetch(aiSuggestPriceUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': aiCsrf, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                }).then(function(r) { return r.json(); }).then(function(data) {
                    btn.disabled = false;
                    box.style.display = 'block';
                    if (!data.success) {
                        rangeEl.textContent = data.error || '{{ __("Could not get suggestion.") }}';
                        return;
                    }
                    var low = data.price_low != null ? Number(data.price_low) : null;
                    var high = data.price_high != null ? Number(data.price_high) : null;
                    var mid = (low != null && high != null && !isNaN(low) && !isNaN(high)) ? Math.round((low + high) / 2) : null;
                    rangeEl.textContent = (low != null && high != null) ? (low.toLocaleString() + ' – ' + high.toLocaleString()) : (data.price_low + ' – ' + data.price_high);
                    justEl.textContent = data.justification || '';
                    if (discEl) discEl.textContent = data.disclaimer || '{{ __("This is a suggestion only; final price is at your discretion.") }}';
                    if (mid != null) {
                        var priceInput = document.querySelector('input[name="price"]');
                        if (priceInput && (!priceInput.value || priceInput.value.trim() === '')) priceInput.value = mid;
                    }
                }).catch(function() {
                    btn.disabled = false;
                    box.style.display = 'block';
                    rangeEl.textContent = '{{ __("Request failed.") }}';
                });
            });
        });
        var socialCopyBtn = document.getElementById('aiGenerateSocialCopyBtn');
        if (socialCopyBtn) {
            socialCopyBtn.addEventListener('click', function() {
                var propertyId = this.getAttribute('data-property-id');
                if (!propertyId) return;
                socialCopyBtn.disabled = true;
                var url = '{{ route("ai.assistant.generate_social_copy") }}';
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': aiCsrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ property_id: parseInt(propertyId, 10) })
                }).then(function(r) { return r.json(); }).then(function(data) {
                    socialCopyBtn.disabled = false;
                    if (data.success) {
                        var fb = document.getElementById('socialCopyFacebook');
                        var ig = document.getElementById('socialCopyInstagram');
                        var li = document.getElementById('socialCopyLinkedin');
                        var ht = document.getElementById('socialCopyHashtags');
                        if (fb) fb.value = data.facebook || '';
                        if (ig) ig.value = data.instagram || '';
                        if (li) li.value = data.linkedin || '';
                        var tw = document.getElementById('socialCopyTwitter');
                        if (tw) tw.value = data.twitter || '';
                        var tiktokEl = document.getElementById('socialCopyTiktok');
                        if (tiktokEl) tiktokEl.value = data.instagram || data.tiktok || '';
                        if (ht) ht.value = data.hashtags || '';
                        $('#socialCopyModal').modal('show');
                    } else {
                        if (typeof $.notify === 'function') {
                            $.notify({ message: data.error || '{{ __("Failed to generate.") }}', title: '', icon: 'fa fa-exclamation' }, { type: 'warning' });
                        }
                    }
                }).catch(function() {
                    socialCopyBtn.disabled = false;
                });
            });
        }
        document.querySelectorAll('.btn-add-post-social').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var platform = this.getAttribute('data-platform');
                var textTargetId = this.getAttribute('data-text-target');
                var ta = document.getElementById(textTargetId);
                var text = ta ? (ta.value || '').trim() : '';
                var hashtagsEl = document.getElementById('socialCopyHashtags');
                var hashtags = hashtagsEl ? (hashtagsEl.value || '').trim() : '';
                if (hashtags) text = text ? (text + ' ' + hashtags) : hashtags;
                if (!text) {
                    if (typeof bootnotify !== 'undefined') bootnotify('{{ __("Enter or generate copy first.") }}', '{{ __("Error") }}', 'warning');
                    return;
                }
                var mediaSelect = document.getElementById('socialCopyMediaSelect');
                var imageUrl = mediaSelect ? (mediaSelect.value || '').trim() : '';
                if (platform === 'instagram' && !imageUrl && typeof bootnotify !== 'undefined') {
                    bootnotify('{{ __("Select an image above for Instagram or add a property image.") }}', '{{ __("Error") }}', 'warning');
                    return;
                }
                this.disabled = true;
                var url = '{{ route("ai.assistant.post_to_social") }}';
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': aiCsrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ platform: platform, text: text, image_url: imageUrl || undefined, hashtags: hashtags })
                }).then(function(r) { return r.json(); }).then(function(data) {
                    btn.disabled = false;
                    if (data.success && typeof bootnotify !== 'undefined') bootnotify(data.message, '{{ __("Success") }}', 'success');
                    else if (data.error && typeof bootnotify !== 'undefined') bootnotify(data.error, '{{ __("Error") }}', 'danger');
                }).catch(function() { btn.disabled = false; });
            });
        });
        document.querySelectorAll('.btn-add-post-tiktok').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var captionEl = document.getElementById('socialCopyTiktok');
                var hashtagsEl = document.getElementById('socialCopyHashtags');
                var caption = captionEl ? (captionEl.value || '').trim() : '';
                var hashtags = hashtagsEl ? (hashtagsEl.value || '').trim() : '';
                var text = hashtags ? (caption ? caption + ' ' + hashtags : hashtags) : caption;
                var copied = false;
                if (text) {
                    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                        navigator.clipboard.writeText(text).then(function() { copied = true; }, function() {});
                    }
                    if (!copied && captionEl) {
                        captionEl.select();
                        try { copied = document.execCommand('copy'); } catch (e) {}
                    }
                }
                window.open('https://www.tiktok.com/upload', '_blank');
                if (typeof bootnotify !== 'undefined') {
                    bootnotify('{{ __("Caption copied. Add your video on TikTok — post created successfully.") }}', '{{ __("Success") }}', 'success');
                }
            });
        });
    })();
    </script>
    @endif
@endsection

