<?php
/*
Plugin Name: Question Builder
Description: Adds a metabox to post editor for dragging and dropping question templates into the content area.
Version: v1.0
Author: Crows Tv
Author URI: https://crowstv.com
*/

function sqp_add_question_metabox()
{
    add_meta_box(
        'sqp_question_metabox',
        'Add New Question',
        'sqp_render_question_metabox',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'sqp_add_question_metabox');

function sqp_add_question_result_metabox()
{
    add_meta_box(
        'sqp_question_result_metabox',
        'Add Question Result',
        'sqp_render_question_result_metabox',
        'post',
        'side',
        'core'
    );
}
add_action('add_meta_boxes', 'sqp_add_question_result_metabox');



function sqp_render_question_metabox($post)
{
    ?>
    <div id="question-template" draggable="true" ondragstart="drag(event)">
        <div class="question-template" style="padding:10px; border:1px dashed #ccc;">
            If you want to create a question, drag it to the content area.
        </div>
    </div>

    <script>
        function drag(event) {

            const questionId = 'soru_' + Date.now();

            event.dataTransfer.setData("text/html", `
                <h3>Write a Question</h3>
                <div style="margin:10px 0 !important;" class="soru" id="${questionId}">
                    <ul style="list-style-type: none !important; padding:0 !important;">
                        <li style="margin: 5px 0 !important; transition: background-color 0.3s !important;">
                            <label style="padding:10px !important; border:1px solid #b506b56b !important; display:block !important; cursor:pointer !important; background:#b506b56b !important;">
                                <input type="radio" name="${questionId}" value="A"> Option A
                            </label>
                        </li>
                        <li style="margin: 5px 0 !important; transition: background-color 0.3s !important;">
                            <label style="padding:10px !important; border:1px solid #b506b56b !important; display:block !important; cursor:pointer !important; background:#b506b56b !important;">
                                <input type="radio" name="${questionId}" value="B"> Option B
                            </label>
                        </li>
                        <li style="margin: 5px 0 !important; transition: background-color 0.3s !important;">
                            <label style="padding:10px !important; border:1px solid #b506b56b !important; display:block !important; cursor:pointer !important; background:#b506b56b !important;">
                                <input type="radio" name="${questionId}" value="C"> Option C
                            </label>
                        </li>
                        <li style="margin: 5px 0 !important; transition: background-color 0.3s !important;">
                            <label style="padding:10px !important; border:1px solid #b506b56b !important; display:block !important; cursor:pointer !important; background:#b506b56b !important;">
                                <input type="radio" name="${questionId}" value="D"> Option D
                            </label>
                        </li>
                    </ul>
                </div>
            `);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const editor = document.getElementById('content');
            editor.addEventListener('drop', function (event) {
                event.preventDefault();
                const data = event.dataTransfer.getData("text/html");

                // Editörün mevcut içeriğine sürüklenen içeriği ekliyoruz
                editor.value += data;
            });

            editor.addEventListener('dragover', function (event) {
                event.preventDefault();
            });
        });
    </script>


    <style>
        .question-template {
            cursor: move;
            background-color: #f9f9f9;
        }
    </style>
    <?php
}

function sqp_render_question_result_metabox($post)
{
    ?>
    <div id="question-result-template" draggable="true" ondragstart="dragResult(event)">
        <div class="question-template" style="padding:10px; border:1px dashed #ccc;">
            create a result for the question, drag it to the content area.
        </div>
    </div>

    <script>
        function dragResult(event) {
            event.dataTransfer.setData("text/html", `
                <div id="result-a" class="resultDiv">
                    <p>Result A Content.</p>
                </div>
                <div id="result-b" class="resultDiv">
                    <p>Result B Content.</p>
                </div>
                <div id="result-c" class="resultDiv">
                   <p> Result C Content.</p>
                </div>
                <div id="result-d" class="resultDiv">
                   <p> Result D Content.</p>
                </div>
            `);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const editor = document.getElementById("content");
            editor.addEventListener("drop", function(event) {
                event.preventDefault();
                const data = event.dataTransfer.getData("text/html");

                editor.value += data;
            });

            editor.addEventListener("dragover", function (event) {
                event.preventDefault();
            });
        });
    </script>
    <style>
        .question-template {
            cursor: move;
            background-color: #f9f9f9;
        }
    </style>
    <?php
}
