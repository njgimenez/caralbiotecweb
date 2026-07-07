<?php $this->layout('shared::layout', ['title' => 'Iniciar Sesión - Caral Biotec']) ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h2 class="text-center fw-bold text-success mb-4">Iniciar Sesión</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $this->e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="/login" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="nombre@correo.com" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary-custom w-100 py-2">Ingresar</button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top text-center text-muted">
                        <p class="mb-1" style="font-size: 0.85rem;"><strong>Credenciales de prueba:</strong></p>
                        <p class="mb-0" style="font-size: 0.85rem;">Usuario: <code>admin@caralbiotec.com</code></p>
                        <p class="mb-0" style="font-size: 0.85rem;">Contraseña: <code>admin123</code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
