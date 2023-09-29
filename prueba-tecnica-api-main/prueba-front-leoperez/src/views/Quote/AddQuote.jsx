import React, { Component } from 'react';

class AddQuote extends Component {
    state = {
        products: [],
        quoteDetails: {
            customerName: '',
            customerLastName: '',
            customerAddress: '',
            quoteItems: [],
        },
        total: 0,
    };

    getProducts() {
        fetch('http://localhost:8000/products')
            .then((response) => response.json())
            .then((data) => {
                this.setState({products: data});
            })
            .catch ((error) => {
                console.error('Error al obtener productos', error);
            });
    }
    
    render() {
        const {products} = this.state;
        return (
            <div>
                <h2>Crear Cotización</h2>
                <form>
                    <label htmlFor="name">Nombre</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                    />
                    <label htmlFor="lastName">Apellido</label>
                    <input
                        type="text"
                        id="lastName"
                        name="lastName"
                        required
                    />
                    <label htmlFor="address">Dirección</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        required
                    />
                    <label htmlFor="date">Fecha</label>
                    <input
                        type="date"
                        id="date"
                        name="date"
                        required />
                </form>
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Valor unit</th>
                                <th>Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.map((product) => (
                                <tr key={product.product_id}>
                                    <td>{product.product_name}</td>
                                    <td>
                                        <select>$({products.map})</select>
                                    </td>
                                    <td>{product.product_price}</td>
                                    <td>*{product.product_price}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <p>TOTAL</p>
                    <button>COTIZAR</button>
                </div>
            </div>
        );
    }
}

export default AddQuote;