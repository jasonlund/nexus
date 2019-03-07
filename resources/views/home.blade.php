@extends('layouts.app')

@section('content')
    <home>
        <template slot="content">
        	<div class="relative w-full">
	            <transition
		            name="router-anim"
		            appear
		            appear-active-class="animated fadeIn"
		            enter-active-class="animated fadeIn"
	            >
	                <router-view></router-view>
	            </transition>
        	</div>
        </template>
    </home>
@endsection
