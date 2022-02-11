<template>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Categories</h1>
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="#">Home</a>
                            </li>
                            <li class="breadcrumb-item active">
                                Categories v1
                            </li>
                        </ol>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="m-0 p-0">Test</div>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                data-toggle="modal"
                                data-target="#exampleModal"
                            >
                                Create new
                            </button>
                        </div>
                        <!-- Modal -->
                        <div
                            class="modal fade"
                            id="exampleModal"
                            tabindex="-1"
                            role="dialog"
                            aria-labelledby="exampleModalLabel"
                            aria-hidden="true"
                        >
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5
                                            class="modal-title"
                                            id="exampleModalLabel"
                                        >
                                            Modal title
                                        </h5>
                                        <button
                                            type="button"
                                            class="close"
                                            data-dismiss="modal"
                                            aria-label="Close"
                                        >
                                            <span aria-hidden="true"
                                                >&times;</span
                                            >
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form
                                            v-on:submit.prevent="createCategory"
                                        >
                                            <div class="form-group">
                                                <label for="exampleInputtext1"
                                                    >Email address</label
                                                >
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="name"
                                                    aria-describedby="textHelp"
                                                    placeholder="Enter text"
                                                    v-model="categoryData.name"
                                                />
                                                <small
                                                    id="textHelp"
                                                    class="form-text text-muted"
                                                    >We'll never share your text
                                                    with anyone else.</small
                                                >
                                            </div>
                                            <div class="form-group">
                                                <label
                                                    for="exampleFormControlFile1"
                                                    >Example file input</label
                                                >
                                                <div>
                                                    <img src="" alt="" srcset="" ref='newCategoryImageDisplay' style="width:150px;">
                                                </div>

                                                <input
                                                    type="file"
                                                    class="form-control-file"
                                                    id="image"
                                                    v-on:change="attachImage"
                                                    ref='newCategoryImage'
                                                />
                                            </div>

                                            <button
                                                type="submit"
                                                class="btn btn-primary btn-sm"
                                            >
                                                Submit
                                            </button>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-dismiss="modal"
                                        >
                                            Close
                                        </button>
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.Modal -->
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Task</th>
                                    <th>Progress</th>
                                    <th style="width: 40px">Label</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1.</td>
                                    <td>Update software</td>
                                    <td>
                                        <div class="progress progress-xs">
                                            <div
                                                class="progress-bar progress-bar-danger"
                                                style="width: 55%"
                                            ></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">55%</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>Clean database</td>
                                    <td>
                                        <div class="progress progress-xs">
                                            <div
                                                class="progress-bar bg-warning"
                                                style="width: 70%"
                                            ></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"
                                            >70%</span
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>Cron job running</td>
                                    <td>
                                        <div
                                            class="progress progress-xs progress-striped active"
                                        >
                                            <div
                                                class="progress-bar bg-primary"
                                                style="width: 30%"
                                            ></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"
                                            >30%</span
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <td>4.</td>
                                    <td>Fix and squish bugs</td>
                                    <td>
                                        <div
                                            class="progress progress-xs progress-striped active"
                                        >
                                            <div
                                                class="progress-bar bg-success"
                                                style="width: 90%"
                                            ></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"
                                            >90%</span
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <li class="page-item">
                                <a class="page-link" href="#">&laquo;</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">1</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">3</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">&raquo;</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: "category",
    data() {
        return {
            categoryData: {
                name: "",
                image: ""
            }
        };
    },
    methods: {
        attachImage() {
            this.categoryData.image = this.$refs.newCategoryImage.files[0];
            let reader = new FileReader();
            reader.addEventListener('load', function(){
                this.$refs.newCategoryImageDisplay.src = reader.result;
            }.bind(this), false);

            reader.readAsDataURL(this.categoryData.image);
        },
        createCategory() {
            console.log("form submitted");
        }
    }
};
</script>
