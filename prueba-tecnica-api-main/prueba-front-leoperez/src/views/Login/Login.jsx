import React, { useState } from 'react';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    return (
        <main className='form-login'>
            <section className='form-container'>
                <h2>Iniciar Sesión</h2>
                <form action='submit'>
                    <div className='input-box'>
                        <label htmlFor='email'>Correo electrónico:</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                        />
                    </div>
                    <div className='input-box'>
                        <label htmlFor="password">Contraseña:</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                        />
                    </div>
                    <button type="submit">Ingresar</button>
                </form>
            </section>
        </main>
    )
}

export default Login