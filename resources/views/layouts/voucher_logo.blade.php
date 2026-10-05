<style>
    .logo-container { 
        margin: -30px -30px 20px -30px; 
        padding: 0; 
        border-bottom: 2px solid #eee; 
        overflow: hidden; 
        border-radius: 8px 8px 0 0;
    }
    .logo-container img { 
        width: 100%; 
        height: auto; 
        display: block; 
    }
</style>

<div class="logo-container">
    <img src="{{ asset('img/logo.jpg') }}" alt="Company Logo" onerror="this.src='{{ asset('images/logo.jpg') }}'; this.onerror=function(){this.src='{{ asset('images/Logo.jpg') }}';}">
</div>
