@extends('statamic::layout')

@section('title', __('Edit ActiveCampaign'))

@section('content')

    <activecampaign-publish-form
        title="ActiveCampaign"
        initial-action="{{ $action }}"
        method="patch"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-meta='{{ empty($meta) ? '{}' : json_encode($meta) }}'
        :initial-values='{{ empty($values) ? '{}' : json_encode($values) }}'
        :initial-localizations="{{ json_encode($localizations) }}"
        initial-site="{{ $site }}"
    ></activecampaign-publish-form>

@stop
