@extends('layouts.app')

@section('content')
    <transition name="fade">
        <home>
            <template slot="content">
                <router-view></router-view>
            </template>
        </home>
    </transition>
@endsection
