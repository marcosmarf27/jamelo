--
-- PostgreSQL database dump
--

-- Dumped from database version 13.1
-- Dumped by pg_dump version 13.1

-- Started on 2021-01-15 13:33:25

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 225 (class 1259 OID 16506)
-- Name: estado; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.estado (
    id integer NOT NULL,
    nome character varying(100),
    uf character varying(2)
);


ALTER TABLE public.estado OWNER TO postgres;

--
-- TOC entry 3090 (class 0 OID 16506)
-- Dependencies: 225
-- Data for Name: estado; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.estado (id, nome, uf) VALUES (12, 'Acre', 'AC');
INSERT INTO public.estado (id, nome, uf) VALUES (27, 'Alagoas', 'AL');
INSERT INTO public.estado (id, nome, uf) VALUES (13, 'Amazonas', 'AM');
INSERT INTO public.estado (id, nome, uf) VALUES (16, 'Amapá', 'AP');
INSERT INTO public.estado (id, nome, uf) VALUES (29, 'Bahia', 'BA');
INSERT INTO public.estado (id, nome, uf) VALUES (23, 'Ceará', 'CE');
INSERT INTO public.estado (id, nome, uf) VALUES (53, 'Distrito Federal', 'DF');
INSERT INTO public.estado (id, nome, uf) VALUES (32, 'Espirito Santo', 'ES');
INSERT INTO public.estado (id, nome, uf) VALUES (52, 'Goiás', 'GO');
INSERT INTO public.estado (id, nome, uf) VALUES (21, 'Maranhão', 'MA');
INSERT INTO public.estado (id, nome, uf) VALUES (31, 'Minas Gerais', 'MG');
INSERT INTO public.estado (id, nome, uf) VALUES (50, 'Mato Grosso do Sul', 'MS');
INSERT INTO public.estado (id, nome, uf) VALUES (51, 'Mato Grosso', 'MT');
INSERT INTO public.estado (id, nome, uf) VALUES (15, 'Pará', 'PA');
INSERT INTO public.estado (id, nome, uf) VALUES (25, 'Paraíba', 'PB');
INSERT INTO public.estado (id, nome, uf) VALUES (26, 'Pernambuco', 'PE');
INSERT INTO public.estado (id, nome, uf) VALUES (22, 'Piauí', 'PI');
INSERT INTO public.estado (id, nome, uf) VALUES (41, 'Paraná', 'PR');
INSERT INTO public.estado (id, nome, uf) VALUES (33, 'Rio de Janeiro', 'RJ');
INSERT INTO public.estado (id, nome, uf) VALUES (24, 'Rio Grande do Norte', 'RN');
INSERT INTO public.estado (id, nome, uf) VALUES (11, 'Rondônia', 'RO');
INSERT INTO public.estado (id, nome, uf) VALUES (14, 'Roraima', 'RR');
INSERT INTO public.estado (id, nome, uf) VALUES (43, 'Rio Grande do Sul', 'RS');
INSERT INTO public.estado (id, nome, uf) VALUES (42, 'Santa Catarina', 'SC');
INSERT INTO public.estado (id, nome, uf) VALUES (28, 'Sergipe', 'SE');
INSERT INTO public.estado (id, nome, uf) VALUES (35, 'São Paulo', 'SP');
INSERT INTO public.estado (id, nome, uf) VALUES (17, 'Tocantins', 'TO');


--
-- TOC entry 2959 (class 2606 OID 16594)
-- Name: estado estado_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estado
    ADD CONSTRAINT estado_pkey PRIMARY KEY (id);


-- Completed on 2021-01-15 13:33:26

--
-- PostgreSQL database dump complete
--

