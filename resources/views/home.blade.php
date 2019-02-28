@extends('layouts.app')

@section('content')
    <home>
        <template slot="content">
            <transition name="fade" mode="out-in">
                <router-view></router-view>
            </transition>
        </template>
    </home>
@endsection
