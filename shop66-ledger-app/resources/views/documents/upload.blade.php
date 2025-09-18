@extends('layouts.app')

@section('page-title', 'Upload Documents')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">Upload</li>
@endsection

@push('styles')
<style>
.dropzone {
    border: 2px dashed #007bff;
    border-radius: 10px;
    background-color: #f8f9fa;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dropzone:hover,
.dropzone.dragover {
    border-color: #0056b3;
    background-color: #e7f1ff;
    color: #0056b3;
}

.file-preview {
    max-height: 400px;
    overflow-y: auto;
}

.file-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 15px;
}

.progress {
    height: 5px;
    margin-top: 10px;
}
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload Documents</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">Supported: PDF, JPG, PNG, WEBP (Max: 10MB)</span>
                    </div>
                </div>
                <div class="card-body">
                    <form id="documentUploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="dropzone text-center p-5 mb-4" id="dropzone">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <h4>Drag & Drop Files Here</h4>
                            <p class="mb-3">or</p>
                            <input type="file" id="fileInput" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" style="display: none;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-folder-open"></i> Browse Files
                            </button>
                        </div>

                        <div id="filePreview" class="file-preview" style="display: none;">
                            <h5>Selected Files:</h5>
                            <div id="fileList"></div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_type">Document Type</label>
                                    <select class="form-control" id="document_type" name="document_type">
                                        <option value="invoice">Invoice</option>
                                        <option value="receipt">Receipt</option>
                                        <option value="purchase_order">Purchase Order</option>
                                        <option value="expense_report">Expense Report</option>
                                        <option value="bank_statement">Bank Statement</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select class="form-control" id="priority" name="priority">
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any notes about these documents..."></textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="auto_process" name="auto_process" checked>
                                <label class="custom-control-label" for="auto_process">
                                    Automatically process with OCR and AI extraction
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="clearFiles()">
                                <i class="fas fa-times"></i> Clear All
                            </button>
                            <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                <i class="fas fa-upload"></i> Upload Documents
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload Progress</h3>
                </div>
                <div class="card-body">
                    <div id="uploadStatus" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i>
                        <p>Select files to begin upload</p>
                    </div>
                    <div id="overallProgress" style="display: none;">
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Overall Progress: <span id="progressText">0%</span></small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Processing Queue</h3>
                </div>
                <div class="card-body">
                    <div class="timeline" id="processingTimeline">
                        <div class="text-muted text-center">
                            <i class="fas fa-clock"></i>
                            <p>No documents in queue</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tips</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Best quality: PDF or high-res images</li>
                        <li><i class="fas fa-check text-success"></i> Ensure text is clearly readable</li>
                        <li><i class="fas fa-check text-success"></i> Include full document (all pages)</li>
                        <li><i class="fas fa-info text-info"></i> Processing typically takes 30-60 seconds</li>
                        <li><i class="fas fa-info text-info"></i> You'll be notified when review is needed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let selectedFiles = [];
let uploadQueue = [];

$(document).ready(function() {
    setupDropzone();
    setupFileInput();
    setupFormSubmission();
});

function setupDropzone() {
    const dropzone = document.getElementById('dropzone');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
    });

    dropzone.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropzone.classList.add('dragover');
    }

    function unhighlight() {
        dropzone.classList.remove('dragover');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
}

function setupFileInput() {
    document.getElementById('fileInput').addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });
}

function handleFiles(files) {
    for (let file of files) {
        if (validateFile(file)) {
            selectedFiles.push(file);
        }
    }
    updateFilePreview();
    updateUploadButton();
}

function validateFile(file) {
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSize = 10 * 1024 * 1024; // 10MB

    if (!allowedTypes.includes(file.type)) {
        alert(`File type not supported: ${file.name}`);
        return false;
    }

    if (file.size > maxSize) {
        alert(`File too large: ${file.name} (max 10MB)`);
        return false;
    }

    return true;
}

function updateFilePreview() {
    const preview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    
    if (selectedFiles.length === 0) {
        preview.style.display = 'none';
        return;
    }

    preview.style.display = 'block';
    fileList.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-file-${getFileIcon(file.type)} text-primary"></i>
                    <strong>${file.name}</strong>
                    <br>
                    <small class="text-muted">${formatFileSize(file.size)}</small>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="progress mt-2" style="display: none;">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        `;
        fileList.appendChild(fileItem);
    });
}

function getFileIcon(type) {
    if (type === 'application/pdf') return 'pdf';
    if (type.startsWith('image/')) return 'image';
    return 'alt';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFilePreview();
    updateUploadButton();
}

function clearFiles() {
    selectedFiles = [];
    document.getElementById('fileInput').value = '';
    updateFilePreview();
    updateUploadButton();
}

function updateUploadButton() {
    const uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.disabled = selectedFiles.length === 0;
}

function setupFormSubmission() {
    document.getElementById('documentUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (selectedFiles.length === 0) {
            alert('Please select files to upload');
            return;
        }

        uploadFiles();
    });
}

function uploadFiles() {
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadStatus = document.getElementById('uploadStatus');
    const overallProgress = document.getElementById('overallProgress');
    
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    
    uploadStatus.innerHTML = '<i class="fas fa-upload text-primary"></i><p>Uploading files...</p>';
    overallProgress.style.display = 'block';

    const formData = new FormData();
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    formData.append('document_type', document.getElementById('document_type').value);
    formData.append('priority', document.getElementById('priority').value);
    formData.append('notes', document.getElementById('notes').value);
    formData.append('auto_process', document.getElementById('auto_process').checked ? '1' : '0');

    selectedFiles.forEach((file, index) => {
        formData.append(`documents[${index}]`, file);
    });

    fetch('{{ route("documents.store") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            uploadStatus.innerHTML = '<i class="fas fa-check text-success"></i><p>Upload completed!</p>';
            updateProgressBar(100);
            
            // Clear form
            setTimeout(() => {
                clearFiles();
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Documents';
                overallProgress.style.display = 'none';
                uploadStatus.innerHTML = '<i class="fas fa-info-circle"></i><p>Select files to begin upload</p>';
                
                // Show success message
                alert('Documents uploaded successfully! Processing will begin shortly.');
                
                // Redirect to documents list or show processing status
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }, 2000);
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        uploadStatus.innerHTML = '<i class="fas fa-times text-danger"></i><p>Upload failed</p>';
        alert('Upload failed: ' + error.message);
        
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Documents';
    });
}

function updateProgressBar(percent) {
    const progressBar = document.querySelector('#overallProgress .progress-bar');
    const progressText = document.getElementById('progressText');
    
    progressBar.style.width = percent + '%';
    progressText.textContent = percent + '%';
}
</script>
@endpush