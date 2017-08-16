-- Table: public.customer

-- DROP TABLE public.customer;

CREATE TABLE public.customer
(
  id serial NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  email character varying(100) NOT NULL,
  CONSTRAINT customer_pkey PRIMARY KEY (id)
)
WITH (
OIDS=FALSE
);
ALTER TABLE public.customer
OWNER TO "o2-carpool";
