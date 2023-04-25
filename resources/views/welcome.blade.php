<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        @vite(['resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="bg-dark py-4">
            <div class="container">
                <header class="d-flex justify-content-between">
                    <p class="fs-2 text-white">User Table</p>
                    <div>
                        <button type="button" class="btn btn-primary fs-5" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Create User
                        </button>
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="user_form">
                                            <p>User creation form</p>
                                            <div id="user_error_box"></div>
                                            <div class="mt-2">
                                                <label for="user_name">User name</label>
                                                <input required id="user_name" type="text" class="form-control" name="name">
                                            </div>
                                            <div class="mt-2">
                                                <label for="user_email">User email</label>
                                                <input required id="user_email" class="form-control" name="email" type="email">
                                            </div>
                                            <div class="mt-2">
                                                <label for="user_phone">User phone</label>
                                                <input required id="user_phone" class="form-control" name="phone" type="text">
                                            </div>
                                            <div class="mt-2">
                                                <label for="position_id">User position</label>
                                                <select class="form-select" aria-label="Default select example" id="positions_list" name="position_id">
                                                </select>
                                            </div>
                                            <div class="mt-2">
                                                <label for="formFile" class="form-label">Select user image</label>
                                                <input class="form-control" type="file" id="formFile" name="photo">
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button id="save_info_btn" type="button" class="btn btn-primary">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <div id="user-table">

                </div>
                <div class="mt-4 d-flex justify-content-between w-50 mx-auto" id="pagination-block">
                    <div><button class="btn btn-light" id="prev_btn">Prev</button></div>
                    <div><button class="btn btn-light" id="next_btn">Next</button></div>
                </div>
            </div>
        </div>
    </body>
    <script type="module">
        const count = 6;

        let currPage = `api/users?page=1&count=${count}`;

        function generateUsers(userData) {
            let userBlocks = [];

            let prev_btn = document.getElementById('prev_btn');

            prev_btn.setAttribute('target_link', userData['prev_link']);

            let next_btn = document.getElementById('next_btn');

            next_btn.setAttribute('target_link', userData['next_link']);

            for (const userKey in userData['users']) {
                let user = userData['users'][userKey];
                userBlocks.push(`<div class="d-flex flex-column flex-md-row rounded-2 bg-light my-1 p-4 w-50 mx-auto justify-content-between">
                    <div class="">
                        <p class="text-info">${user['position']}</p>
                        <p class="fs-5">${user['name']}</p>
                        <p>Phone: <span class="fw-bold">${user['phone']}</span></p>
                        <p>Email: <span class="fw-bold">${user['email']}</span></p>
                    </div>
                    <div class="">
                        <div class="rounded-circle overflow-hidden mx-auto" style="width: 80px">
                        <img width="80px" src="/images/${user['photo']}" alt="">
                    </div>
                    </div>
                </div>`)
            }
            document.getElementById('user-table').innerHTML = `${userBlocks.join()}`;
        }
        async function generateTable(link) {
            fetch(link).then(resp => resp.json()).then(data => generateUsers(data));
        }

        async function setPositions() {
            let positionList = document.getElementById('positions_list');
            let addPositions = (positionData) => {
                for (const positionDataKey in positionData['positions']) {
                    let option = document.createElement('option');
                    option.value = positionData['positions'][positionDataKey]['id'];
                    option.innerText = positionData['positions'][positionDataKey]['name'];
                    positionList.appendChild(option);
                }
            }
            fetch('/api/positions').then(resp => resp.json()).then(data => addPositions(data));
        }

        document.addEventListener('DOMContentLoaded', async function () {
            await generateTable(currPage);
            await setPositions();
            let prev_btn = document.getElementById('prev_btn');
            let next_btn = document.getElementById('next_btn');
            let saveBtn = document.getElementById('save_info_btn');
            saveBtn.addEventListener('click', function (ev) {
                let userForm = document.getElementById('user_form');
                let formData = new FormData(userForm);
                fetch('/api/token').then(() => {
                    fetch('/api/users', {
                        method: 'post',
                        body: formData
                    }).then(resp => [resp.json(), resp.status]).then(async ([data, status]) => {
                        if(status >= 400) {
                            let errorBox = document.getElementById('user_error_box');
                            errorBox.innerHTML = "";
                            data = await data;
                            console.log(data['fails'])
                            if(data.hasOwnProperty('message')){
                                let error = data['message'];
                                let p = document.createElement('p');
                                p.innerText = error;
                                p.classList.add('text-danger');
                                errorBox.appendChild(p);
                            }
                            if(data.hasOwnProperty('fails')){
                                let errors = data['fails'];
                                for (const errorsKey in errors) {
                                    let error = errors[errorsKey];
                                    let p = document.createElement('p');
                                    p.innerText = error;
                                    p.classList.add('text-danger');
                                    errorBox.appendChild(p);
                                }
                            }
                        }
                    })
                })
            });
            [prev_btn, next_btn].forEach((el) => {
                el.addEventListener('click', async () => {
                    if(el.getAttribute('target_link') !== 'null') {

                        await generateTable(el.getAttribute('target_link'));
                    }
                })
            })
        })
    </script>
</html>
