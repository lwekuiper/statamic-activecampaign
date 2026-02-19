@extends('statamic::layout')

@section('title', __('Edit ActiveCampaign'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('activecampaign.index'),
        'title' => 'ActiveCampaign'
    ])

    <activecampaign-publish-form
        title="{{ $title }}"
        initial-action="{{ $action }}"
        initial-delete-url="{{ $deleteUrl }}"
        initial-listing-url="{{ $listingUrl }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-meta='{{ empty($meta) ? '{}' : json_encode($meta) }}'
        :initial-values='{{ empty($values) ? '{}' : json_encode($values) }}'
        :initial-localizations="{{ empty($localizations) ? '[]' : json_encode($localizations) }}"
        initial-site="{{ empty($locale) ? '' : $locale }}"
        :initial-has-origin="{{ json_encode($hasOrigin ?? false) }}"
        :initial-origin-values='{{ json_encode($originValues ?? null) }}'
        :initial-origin-meta='{{ json_encode($originMeta ?? null) }}'
        :initial-localized-fields="{{ json_encode($localizedFields ?? []) }}"
    ></activecampaign-publish-form>

@stop
