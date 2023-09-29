import { Navigate, Routes, Route } from 'react'
import Login from './views/Login/Login'
import AddQuote from './views/Quote/AddQuote'
import ListQuote from './views/Quote/ListQuote'
import './App.css'

function App() {
  return (
    <>
      <Routes>
        <Route path="/" element={<Navigate to="/login" replace />} />
        <Route path="/login" element={<Login />} />
        <Route path="/add-quote" element={<AddQuote />} />
        <Route path="/list-quote" element={<ListQuote />} />
      </Routes>
    </>
  );
}

export default App