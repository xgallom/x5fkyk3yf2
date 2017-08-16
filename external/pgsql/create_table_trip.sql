-- Table: public.trip

-- DROP TABLE public.trip;

CREATE TABLE public.trip
(
  id serial NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  is_approved boolean NOT NULL DEFAULT false,
  customer_id integer NOT NULL,
  CONSTRAINT trip_pkey PRIMARY KEY (id),
  CONSTRAINT trip_fkey_customer_id FOREIGN KEY (customer_id)
      REFERENCES public.customer (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.trip
  OWNER TO "o2-carpool";
