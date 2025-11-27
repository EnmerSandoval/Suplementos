import { useState } from 'react';
import type { FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

export default function Login() {
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      await login({ usuario, contrasena: password });
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.mensaje || err.response?.data?.error || 'Error al iniciar sesión');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-vh-100 d-flex align-items-center justify-content-center"
         style={{ background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }}>
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <div className="card shadow-lg border-0 rounded-4">
              <div className="card-body p-4 p-md-5">
                {/* Logo y título */}
                <div className="text-center mb-4">
                  <div className="bg-primary d-inline-flex align-items-center justify-content-center rounded-circle p-3 mb-3"
                       style={{ width: '80px', height: '80px' }}>
                    <i className="bi bi-box-seam text-white" style={{ fontSize: '2.5rem' }}></i>
                  </div>
                  <h1 className="h2 fw-bold text-dark mb-2">Suplementos</h1>
                  <p className="text-muted">Sistema de Gestión</p>
                </div>

                {/* Formulario */}
                <form onSubmit={handleSubmit}>
                  {error && (
                    <div className="alert alert-danger alert-dismissible fade show" role="alert">
                      <i className="bi bi-exclamation-triangle-fill me-2"></i>
                      {error}
                      <button
                        type="button"
                        className="btn-close"
                        onClick={() => setError('')}
                        aria-label="Close"
                      ></button>
                    </div>
                  )}

                  <div className="mb-3">
                    <label htmlFor="usuario" className="form-label fw-semibold">
                      <i className="bi bi-person-fill me-2"></i>
                      Usuario
                    </label>
                    <input
                      type="text"
                      className="form-control form-control-lg"
                      id="usuario"
                      value={usuario}
                      onChange={(e) => setUsuario(e.target.value)}
                      placeholder="Ingresa tu usuario"
                      required
                      autoComplete="username"
                    />
                  </div>

                  <div className="mb-4">
                    <label htmlFor="password" className="form-label fw-semibold">
                      <i className="bi bi-lock-fill me-2"></i>
                      Contraseña
                    </label>
                    <input
                      type="password"
                      className="form-control form-control-lg"
                      id="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="••••••••"
                      required
                      autoComplete="current-password"
                    />
                  </div>

                  <button
                    type="submit"
                    className="btn btn-primary btn-lg w-100 fw-semibold"
                    disabled={isLoading}
                  >
                    {isLoading ? (
                      <>
                        <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Iniciando sesión...
                      </>
                    ) : (
                      <>
                        <i className="bi bi-box-arrow-in-right me-2"></i>
                        Iniciar Sesión
                      </>
                    )}
                  </button>
                </form>

                <div className="mt-4 text-center">
                  <small className="text-muted">
                    <i className="bi bi-shield-check me-1"></i>
                    Sistema de gestión de inventario y ventas
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
