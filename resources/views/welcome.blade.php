<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Simple Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
</head>
<body>
<div class="container mt-5" x-data="social">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>Search for a username</h2>
            <h6 class="mb-4">Facebook, Instagram, Threads, Tiktok, Reddit</h6>
            <div class="form-group">
                <label for="textInput">Username</label>
                <input x-model="username" class="form-control" id="textInput" name="textInput" type="text" placeholder="Enter username">
            </div>
            <button @click="submit_it()" type="submit" class="btn btn-primary">Search</button>
            <div x-show="isLoading" class="spinner">
                <img src="{{ asset('Spinner@1x-1.0s-200px-200px.gif') }}">
            </div>
            <div class="pt-4" x-show="data_available === 'available'">
                <h5>Username found on the following platforms</h5>
                <ul>
                    <template x-for="item in data" :key="item">
                        <li x-text="item"></li>
                    </template>
                </ul>
            </div>
            <div class="pt-4" x-show="data_available === 'unavailable'">
                <h5>Username not found on any platform</h5>
            </div>
        </div>
    </div>
</div>

<!--&lt;!&ndash; Bootstrap JS (optional, for certain components like dropdowns, modals, etc.) &ndash;&gt;-->
<!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>-->
<!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>-->

<!-- Your custom script (for handling form submission, etc.) -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('social', () => ({
            username: '',
            isLoading: false,
            data: {},
            data_available: '',

            submit_it(){
                this.data_available = '';

                this.isLoading = true;
                const postData = {
                    username: this.username,
                }; // Data to send in the request
                fetch('/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(postData)
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Update responseData with the received data
                        // Alpine.data('responseData', data);
                        this.isLoading = false;
                        if (data.status === 0){
                            this.data = data.social
                            this.data_available = 'available'
                        }else{
                            this.data_available = 'unavailable'
                        }
                    })
                    .catch(error => {
                        console.log('There was a problem with the fetch operation:', error);
                    });
            }
        }));
    });
</script>
</body>
</html>
