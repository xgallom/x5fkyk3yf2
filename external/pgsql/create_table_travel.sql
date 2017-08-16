-- Table: public.travel

-- DROP TABLE public.travel;

CREATE TABLE public.travel
(
  id serial NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  is_approved boolean,
  departure timestamp without time zone NOT NULL,
  travel_type_id integer NOT NULL,
  travel_provider_id integer,
  trip_id integer NOT NULL,
  address character varying(100) DEFAULT NULL::character varying,
  CONSTRAINT travel_pkey PRIMARY KEY (id),
  CONSTRAINT travel_fkey_travel_type_id FOREIGN KEY (travel_type_id)
      REFERENCES public.travel_type (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT travel_fkey_trip_id FOREIGN KEY (trip_id)
      REFERENCES public.trip (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.travel
  OWNER TO "o2-carpool";

-- Index: public.fki_travel_fkey_trip_id

-- DROP INDEX public.fki_travel_fkey_trip_id;

CREATE INDEX fki_travel_fkey_trip_id
  ON public.travel
  USING btree
  (trip_id);

