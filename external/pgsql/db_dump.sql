--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5beta2
-- Dumped by pg_dump version 9.5beta2

-- Started on 2017-08-16 22:19:58

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 2188 (class 1262 OID 16435)
-- Name: o2-carpool-develop; Type: DATABASE; Schema: -; Owner: o2-carpool
--

CREATE DATABASE "o2-carpool-develop" WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'English_United States.1252' LC_CTYPE = 'English_United States.1252';


ALTER DATABASE "o2-carpool-develop" OWNER TO "o2-carpool";

\connect "o2-carpool-develop"

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 194 (class 3079 OID 12355)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 2191 (class 0 OID 0)
-- Dependencies: 194
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 183 (class 1259 OID 16469)
-- Name: city; Type: TABLE; Schema: public; Owner: o2-carpool
--

CREATE TABLE city (
    id integer NOT NULL,
    name character varying(32) NOT NULL,
    coords point NOT NULL,
    has_rental boolean DEFAULT false NOT NULL,
    default_address character varying(100) NOT NULL
);


ALTER TABLE city OWNER TO "o2-carpool";

--
-- TOC entry 182 (class 1259 OID 16467)
-- Name: city_id_seq; Type: SEQUENCE; Schema: public; Owner: o2-carpool
--

CREATE SEQUENCE city_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE city_id_seq OWNER TO "o2-carpool";

--
-- TOC entry 2192 (class 0 OID 0)
-- Dependencies: 182
-- Name: city_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: o2-carpool
--

ALTER SEQUENCE city_id_seq OWNED BY city.id;


--
-- TOC entry 185 (class 1259 OID 16478)
-- Name: customer; Type: TABLE; Schema: public; Owner: o2-carpool
--

CREATE TABLE customer (
    id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    email character varying(100) NOT NULL
);


ALTER TABLE customer OWNER TO "o2-carpool";

--
-- TOC entry 184 (class 1259 OID 16476)
-- Name: customer_id_seq; Type: SEQUENCE; Schema: public; Owner: o2-carpool
--

CREATE SEQUENCE customer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE customer_id_seq OWNER TO "o2-carpool";

--
-- TOC entry 2193 (class 0 OID 0)
-- Dependencies: 184
-- Name: customer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: o2-carpool
--

ALTER SEQUENCE customer_id_seq OWNED BY customer.id;


--
-- TOC entry 189 (class 1259 OID 16500)
-- Name: travel; Type: TABLE; Schema: public; Owner: o2-carpool
--

CREATE TABLE travel (
    id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    is_approved boolean,
    departure timestamp without time zone NOT NULL,
    travel_type_id integer NOT NULL,
    travel_provider_id integer,
    trip_id integer NOT NULL,
    address character varying(100) DEFAULT NULL::character varying
);


ALTER TABLE travel OWNER TO "o2-carpool";

--
-- TOC entry 193 (class 1259 OID 16541)
-- Name: travel_cities; Type: TABLE; Schema: public; Owner: o2-carpool
--

CREATE TABLE travel_cities (
    id integer NOT NULL,
    travel_id integer NOT NULL,
    travel_step integer DEFAULT 1 NOT NULL,
    city_id integer NOT NULL
);


ALTER TABLE travel_cities OWNER TO "o2-carpool";

--
-- TOC entry 192 (class 1259 OID 16539)
-- Name: travel_cities_id_seq; Type: SEQUENCE; Schema: public; Owner: o2-carpool
--

CREATE SEQUENCE travel_cities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE travel_cities_id_seq OWNER TO "o2-carpool";

--
-- TOC entry 2194 (class 0 OID 0)
-- Dependencies: 192
-- Name: travel_cities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: o2-carpool
--

ALTER SEQUENCE travel_cities_id_seq OWNED BY travel_cities.id;


--
-- TOC entry 188 (class 1259 OID 16498)
-- Name: travel_id_seq; Type: SEQUENCE; Schema: public; Owner: o2-carpool
--

CREATE SEQUENCE travel_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE travel_id_seq OWNER TO "o2-carpool";

--
-- TOC entry 2195 (class 0 OID 0)
-- Dependencies: 188
-- Name: travel_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: o2-carpool
--

ALTER SEQUENCE travel_id_seq OWNED BY travel.id;


--
-- TOC entry 187 (class 1259 OID 16487)
-- Name: travel_type; Type: TABLE; Schema: public; Owner: o2-carpool
--

CREATE TABLE travel_type (
    id integer NOT NULL,
    name character varying(16),
    is_provider boolean DEFAULT false NOT NULL
);


ALTER TABLE travel_type OWNER TO "o2-carpool";

--
-- TOC entry 186 (class 1259 OID 16485)
-- Name: travel_type_id_seq; Type: SEQUENCE; Schema: public; Owner: o2-carpool
--

CREATE SEQUENCE travel_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE travel_type_id_seq OWNER TO "o2-carpool";

--
-- TOC entry 2196 (class 0 OID 0)
-- Dependencies: 186
-- Name: travel_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: o2-carpool
--

ALTER SEQUENCE travel_type_id_seq OWNED BY travel_type.id;


--
-- TOC entry 191 (class 1259 OID 16520)
-- Name: trip; Type: TABLE; Schema: public; Owner: o2-carpool
--

CREATE TABLE trip (
    id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    customer_id integer NOT NULL,
    is_approved boolean DEFAULT false NOT NULL
);


ALTER TABLE trip OWNER TO "o2-carpool";

--
-- TOC entry 190 (class 1259 OID 16518)
-- Name: trip_id_seq; Type: SEQUENCE; Schema: public; Owner: o2-carpool
--

CREATE SEQUENCE trip_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE trip_id_seq OWNER TO "o2-carpool";

--
-- TOC entry 2197 (class 0 OID 0)
-- Dependencies: 190
-- Name: trip_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: o2-carpool
--

ALTER SEQUENCE trip_id_seq OWNED BY trip.id;


--
-- TOC entry 181 (class 1259 OID 16457)
-- Name: user; Type: TABLE; Schema: public; Owner: o2-carpool
--

CREATE TABLE "user" (
    id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    active boolean DEFAULT true NOT NULL,
    userlevel integer DEFAULT 0 NOT NULL,
    username character varying(16) NOT NULL,
    password character varying(50) NOT NULL,
    email character varying(100) NOT NULL,
    can_approve_orders boolean DEFAULT false NOT NULL
);


ALTER TABLE "user" OWNER TO "o2-carpool";

--
-- TOC entry 180 (class 1259 OID 16455)
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: o2-carpool
--

CREATE SEQUENCE user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_id_seq OWNER TO "o2-carpool";

--
-- TOC entry 2198 (class 0 OID 0)
-- Dependencies: 180
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: o2-carpool
--

ALTER SEQUENCE user_id_seq OWNED BY "user".id;


--
-- TOC entry 2022 (class 2604 OID 16472)
-- Name: id; Type: DEFAULT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY city ALTER COLUMN id SET DEFAULT nextval('city_id_seq'::regclass);


--
-- TOC entry 2024 (class 2604 OID 16481)
-- Name: id; Type: DEFAULT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY customer ALTER COLUMN id SET DEFAULT nextval('customer_id_seq'::regclass);


--
-- TOC entry 2028 (class 2604 OID 16503)
-- Name: id; Type: DEFAULT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel ALTER COLUMN id SET DEFAULT nextval('travel_id_seq'::regclass);


--
-- TOC entry 2034 (class 2604 OID 16544)
-- Name: id; Type: DEFAULT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel_cities ALTER COLUMN id SET DEFAULT nextval('travel_cities_id_seq'::regclass);


--
-- TOC entry 2026 (class 2604 OID 16490)
-- Name: id; Type: DEFAULT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel_type ALTER COLUMN id SET DEFAULT nextval('travel_type_id_seq'::regclass);


--
-- TOC entry 2031 (class 2604 OID 16523)
-- Name: id; Type: DEFAULT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY trip ALTER COLUMN id SET DEFAULT nextval('trip_id_seq'::regclass);


--
-- TOC entry 2017 (class 2604 OID 16460)
-- Name: id; Type: DEFAULT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY "user" ALTER COLUMN id SET DEFAULT nextval('user_id_seq'::regclass);


--
-- TOC entry 2173 (class 0 OID 16469)
-- Dependencies: 183
-- Data for Name: city; Type: TABLE DATA; Schema: public; Owner: o2-carpool
--



--
-- TOC entry 2199 (class 0 OID 0)
-- Dependencies: 182
-- Name: city_id_seq; Type: SEQUENCE SET; Schema: public; Owner: o2-carpool
--

SELECT pg_catalog.setval('city_id_seq', 1, false);


--
-- TOC entry 2175 (class 0 OID 16478)
-- Dependencies: 185
-- Data for Name: customer; Type: TABLE DATA; Schema: public; Owner: o2-carpool
--



--
-- TOC entry 2200 (class 0 OID 0)
-- Dependencies: 184
-- Name: customer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: o2-carpool
--

SELECT pg_catalog.setval('customer_id_seq', 1, false);


--
-- TOC entry 2179 (class 0 OID 16500)
-- Dependencies: 189
-- Data for Name: travel; Type: TABLE DATA; Schema: public; Owner: o2-carpool
--



--
-- TOC entry 2183 (class 0 OID 16541)
-- Dependencies: 193
-- Data for Name: travel_cities; Type: TABLE DATA; Schema: public; Owner: o2-carpool
--



--
-- TOC entry 2201 (class 0 OID 0)
-- Dependencies: 192
-- Name: travel_cities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: o2-carpool
--

SELECT pg_catalog.setval('travel_cities_id_seq', 1, false);


--
-- TOC entry 2202 (class 0 OID 0)
-- Dependencies: 188
-- Name: travel_id_seq; Type: SEQUENCE SET; Schema: public; Owner: o2-carpool
--

SELECT pg_catalog.setval('travel_id_seq', 1, false);


--
-- TOC entry 2177 (class 0 OID 16487)
-- Dependencies: 187
-- Data for Name: travel_type; Type: TABLE DATA; Schema: public; Owner: o2-carpool
--

INSERT INTO travel_type VALUES (1, 'passenger', false);
INSERT INTO travel_type VALUES (2, 'car_compan', false);
INSERT INTO travel_type VALUES (3, 'car_rental', false);
INSERT INTO travel_type VALUES (4, 'car_person', false);
INSERT INTO travel_type VALUES (5, 'other', false);


--
-- TOC entry 2203 (class 0 OID 0)
-- Dependencies: 186
-- Name: travel_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: o2-carpool
--

SELECT pg_catalog.setval('travel_type_id_seq', 5, true);


--
-- TOC entry 2181 (class 0 OID 16520)
-- Dependencies: 191
-- Data for Name: trip; Type: TABLE DATA; Schema: public; Owner: o2-carpool
--



--
-- TOC entry 2204 (class 0 OID 0)
-- Dependencies: 190
-- Name: trip_id_seq; Type: SEQUENCE SET; Schema: public; Owner: o2-carpool
--

SELECT pg_catalog.setval('trip_id_seq', 1, false);


--
-- TOC entry 2171 (class 0 OID 16457)
-- Dependencies: 181
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: o2-carpool
--



--
-- TOC entry 2205 (class 0 OID 0)
-- Dependencies: 180
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: o2-carpool
--

SELECT pg_catalog.setval('user_id_seq', 1, false);


--
-- TOC entry 2039 (class 2606 OID 16475)
-- Name: city_pkey; Type: CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY city
    ADD CONSTRAINT city_pkey PRIMARY KEY (id);


--
-- TOC entry 2041 (class 2606 OID 16484)
-- Name: customer_pkey; Type: CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY customer
    ADD CONSTRAINT customer_pkey PRIMARY KEY (id);


--
-- TOC entry 2050 (class 2606 OID 16547)
-- Name: travel_cities_pkey; Type: CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel_cities
    ADD CONSTRAINT travel_cities_pkey PRIMARY KEY (id);


--
-- TOC entry 2046 (class 2606 OID 16506)
-- Name: travel_pkey; Type: CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel
    ADD CONSTRAINT travel_pkey PRIMARY KEY (id);


--
-- TOC entry 2043 (class 2606 OID 16492)
-- Name: travel_type_pkey; Type: CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel_type
    ADD CONSTRAINT travel_type_pkey PRIMARY KEY (id);


--
-- TOC entry 2048 (class 2606 OID 16527)
-- Name: trip_pkey; Type: CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY trip
    ADD CONSTRAINT trip_pkey PRIMARY KEY (id);


--
-- TOC entry 2037 (class 2606 OID 16466)
-- Name: user_pkey; Type: CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- TOC entry 2044 (class 1259 OID 16538)
-- Name: fki_travel_fkey_trip_id; Type: INDEX; Schema: public; Owner: o2-carpool
--

CREATE INDEX fki_travel_fkey_trip_id ON travel USING btree (trip_id);


--
-- TOC entry 2055 (class 2606 OID 16553)
-- Name: travel_cities_fkey_city_id; Type: FK CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel_cities
    ADD CONSTRAINT travel_cities_fkey_city_id FOREIGN KEY (city_id) REFERENCES city(id);


--
-- TOC entry 2054 (class 2606 OID 16548)
-- Name: travel_cities_fkey_travel_id; Type: FK CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel_cities
    ADD CONSTRAINT travel_cities_fkey_travel_id FOREIGN KEY (travel_id) REFERENCES travel(id);


--
-- TOC entry 2051 (class 2606 OID 16507)
-- Name: travel_fkey_travel_type_id; Type: FK CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel
    ADD CONSTRAINT travel_fkey_travel_type_id FOREIGN KEY (travel_type_id) REFERENCES travel_type(id);


--
-- TOC entry 2052 (class 2606 OID 16533)
-- Name: travel_fkey_trip_id; Type: FK CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY travel
    ADD CONSTRAINT travel_fkey_trip_id FOREIGN KEY (trip_id) REFERENCES trip(id);


--
-- TOC entry 2053 (class 2606 OID 16528)
-- Name: trip_fkey_customer_id; Type: FK CONSTRAINT; Schema: public; Owner: o2-carpool
--

ALTER TABLE ONLY trip
    ADD CONSTRAINT trip_fkey_customer_id FOREIGN KEY (customer_id) REFERENCES customer(id);


--
-- TOC entry 2190 (class 0 OID 0)
-- Dependencies: 5
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2017-08-16 22:19:59

--
-- PostgreSQL database dump complete
--

